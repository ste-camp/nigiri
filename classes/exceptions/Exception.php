<?php

namespace nigiri\exceptions;

use nigiri\db\DBException;
use nigiri\Email;
use nigiri\Site;
use nigiri\views\Html;

class Exception extends \Exception
{
    const DEFAULT_THEME_KEY = 'default';

    /**
     * @var array|string an array of names of classes and views to use to render the error when it reaches the uncaught exception handler
     * Each entry represents the view to use if the specified theme is the current one.
     * If the current theme is not in the list then the entry with key self::DEFAULT_THEME_KEY is used
     * You can also specify a View by appending its path after the theme with a colon prefix (:)
     * Theme must implement \nigiri\themes\ThemeInterface
     */
    protected $theme = [
        self::DEFAULT_THEME_KEY => 'nigiri\\themes\\FatalErrorTheme',
        'nigiri\\themes\\AjaxTheme:ajax_exception'
    ];

    private $internal;

    public function __construct($str = null, $no = null, $detail = null)
    {
        parent::__construct($str, $no);
        $this->internal = $detail;
    }

    public function showError($return = false)
    {
        $str = '<div class="error">' . Html::escape($this->getMessage()) . "</div>";
        if (!$return) {
            echo $str;
        } else {
            return $str;
        }
    }

    public function showAndLogError($return = false)
    {
        $this->logError();
        $str = '<div class="error">' . Html::escape($this->getMessage()) . "</div>";
        if (!$return) {
            echo $str;
        } else {
            return $str;
        }
    }

    public function logError($additional = '', $save_trace = false)
    {
        $trace = '';
        if ($save_trace) {
            $trace = "\n Call Stack:\n";
            ob_start();
            $t = $this->getTrace();
            var_dump($t);
            $trace .= ob_get_contents();
            ob_end_clean();
        }

        $this->watchdog($additional . ' - ' . $this->renderFullError() . $trace);
    }

    public function renderFullError()
    {
        return $this->getMessage() . " [" . $this->getInternalError() . "]";
    }

    public function getInternalError()
    {
        return $this->internal;
    }

    /**
     * Writes the full error description in the PHP error log, if it's enabled
     */
    public function logToErrorLog()
    {
        error_log($this->renderFullError());
    }

    public function logToWebmasterEmail()
    {
        $email = Site::getParam(NIGIRI_PARAM_TECH_EMAIL);
        if (empty($email)) {
            $email = Site::getParam(NIGIRI_PARAM_EMAIL);
            if (empty($email)) {
                $email = 'webmaster';
            }
        }

        try {
            $m = new Email();
            @$m->addRecipients($email)->send(Site::getParam(NIGIRI_PARAM_SITE_NAME) . ': Errore Fatale!',
              $this->renderEmailText());
        } catch (Exception $e) {
            //Nothing we are already dealing with an error
        }
    }

    protected function renderEmailText()
    {
        ob_start();
        $t = $this->getTrace();
        var_dump($t);
        $stack = ob_get_contents();
        ob_end_clean();

        return "Si è verificato un errore fatale!

    Errore generico: " . $this->getMessage() . "
    Dettaglio Errore: " . $this->renderFullError() . "

    Pagina Richiesta: " . $_GET['front_controller_page'] . "

    Call Stack:" . $stack;
    }

    public function unCaughtEffect()
    {
        header('HTTP/1.0 500 Internal Server Error', true, 500);
    }

    /**
     * Aggiunge una linea nel log degli errori
     * @param $msg : il messaggio da inserire
     * @param $user : opzionale, l'utente che ha eseguito l'azione che ha scatenato l'errore
     */
    static public function watchdog($msg, $user = "")
    {
        if (Site::DB() !== null) {
            try {
                Site::DB()->query("INSERT INTO LogErrori (Nome, Errore, DataEvento, IP) VALUES ('" . Site::DB()->escape($user) . "','" . Site::DB()->escape($msg) . "',NOW(),'" . Site::DB()->escape($_SERVER['REMOTE_ADDR']) . "')");
            } catch (DBException $e) {
                //Se fallisce perfino questo...registriamo l'errore con l'handler di default di PHP
                error_log($msg);
            }
        } else {//No DB enabled
            error_log($msg);
        }
    }

    /**
     * Gets the Theme class configured to handle the screen rendering of this Exception
     * @return string
     */
    public function getThemeClass()
    {
        $className = get_called_class();
        $overrides = Site::getParam(NIGIRI_PARAM_EXCEPTIONS_VIEWS, []);

        if (array_key_exists($className, $overrides)) {
            if(is_array($overrides[$className])){
                return $this->getActiveThemeOverride($className, $overrides[$className]);
            }
            else {
                return $overrides[$className];
            }
        }
        else {
            if(is_array($this->theme)){
                return $this->getActiveThemeOverride($className, $this->theme);
            }
            else {
                return $this->theme;
            }
        }
    }

    private function getActiveThemeOverride($className, $overrides){
        $themeName = '';
        try{
            $theme = Site::getTheme();
            $themeName = get_class($theme);
        }
        catch(Exception $e){//Exception may have been thrown on theme init, let's avoid it
        }

        $default = array_key_exists(self::DEFAULT_THEME_KEY, $overrides) ? $overrides[self::DEFAULT_THEME_KEY]: '';
        $match = '';
        foreach($overrides[$className] as $override){
            if(strpos($override, $themeName) === 0 and (strlen($override) == strlen($themeName) or $override[strlen($themeName)] == ':')) {//if override starts with current theme name then it's the rule to apply
                $match = $override;
            }
        }

        if(empty($match) && empty($default)) {
            return is_array($this->theme) ? null : $this->theme;
        }
        elseif(!empty($match)) {
            return $match;
        }
        else {
            return $default;
        }
    }
}
