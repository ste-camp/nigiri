<?php

namespace nigiri;

use nigiri\db\DB;
use nigiri\exceptions\Exception;
use nigiri\exceptions\ExceptionDbWriter;
use nigiri\rbac\Auth;
use nigiri\themes\ThemeInterface;

/**
 * The main class of the framework. Represents the website and its resources and data
 */
class Site
{
    /**
     * @var DB
     */
    static private $DB = null;
    static private $params = [];

    /**
     * Configuration array to build the actual Theme object
     * @var array
     */
    static private $themeConfig;

    /**
     * @var ThemeInterface
     */
    static private $theme = null;

    /**
     * @var Router
     */
    static private $router;

    /**
     * @var Auth
     */
    static private $auth;

    /** @var Psr4AutoloaderClass */
    static private $autoloader;

    /**
     * @param $data
     * @throws Exception
     * @throws exceptions\InternalServerError
     */
    static function init($data)
    {
        if (empty($data['disableSession'])) {
            session_start();
        }

        if(!empty($data['exception_db_writer']) and $data['exception_db_writer'] instanceof ExceptionDbWriter){
            Exception::setDbWriter($data['exception_db_writer']);
        }

        if (empty($data['default_theme'])) {
            throw new Exception("Nessun tema configurato per visualizzare il sito", 1,
              "Non è stato specificato nessun tema in configurazione");
        } else {
            self::themeClassCheck($data['default_theme']);

            self::$themeConfig = $data['default_theme'];
        }

        if (!empty($data['db'])) {
            self::initDB($data['db']);
        }

        if (!empty($data['params'])) {
            self::$params = $data['params'];
        }

        self::autoloadSetup($data['autoloader'], empty($data['autoload_paths']) ? [] : $data['autoload_paths']);

        self::$router = new Router(empty($data['permanent_plugins']) ? [] : $data['permanent_plugins']);

        if (!empty($data['enableAuth'])) {
            self::$auth = new Auth(empty($data['authUserClass']) ? '' : $data['authUserClass']);
        }

        if (self::getParam(NIGIRI_PARAM_DEBUG)) {
            ini_set('display_errors', true);
        }

        $locale = self::getParam(NIGIRI_PARAM_LOCALES, []);
        if (array_key_exists(self::getRouter()->getRequestedLanguage(), $locale)) {
            call_user_func_array('setlocale',
              array_merge([LC_ALL], $locale[self::getRouter()->getRequestedLanguage()]));

            if (function_exists('gettext') and file_exists(dirname(__DIR__) . '/i18n')) {//Internationalization folder for gettext
                $directory = dirname(__DIR__) . '/i18n';
                $domain = 'messages';
                $i18nLocale = reset($locale[self::getRouter()->getRequestedLanguage()]);

                setlocale(LC_MESSAGES, $i18nLocale);
                bindtextdomain($domain, $directory);
                textdomain($domain);
                bind_textdomain_codeset($domain, 'UTF-8');
            }
        }

        date_default_timezone_set(self::getParam(NIGIRI_PARAM_TIMEZONE, ''));
    }

    /**
     * @return DB
     */
    public static function DB()
    {
        return self::$DB;
    }

    public static function getRouter()
    {
        return self::$router;
    }

    /**
     * @return ThemeInterface
     * @throws Exception
     */
    public static function getTheme()
    {
        if (self::$theme == null) {
            self::$theme = self::buildTheme(self::$themeConfig);
        }

        return self::$theme;
    }

    /**
     * Switches the current theme with another one.
     * WARNING: strings already passed to the theme with append() will be lost!
     * @param ThemeInterface $t
     */
    public static function switchTheme(ThemeInterface $t)
    {
        self::$theme = $t;
    }

    /**
     * @param $config
     * @throws Exception
     */
    public static function switchThemeByConfig($config)
    {
        self::themeClassCheck($config);
        self::$themeConfig = $config;
        self::$theme = null;
    }

    /**
     * @return Auth
     */
    public static function getAuth()
    {
        return self::$auth;
    }

    /**
     * Finds a static site parameter
     * @param $name
     * @param mixed|null $default default value to return if the parameter is not found
     * @return mixed|null
     */
    public static function getParam($name, $default = null)
    {
        return self::getParamRecursive($name, self::$params, $default);
    }

    /**
     * Gets the name of the website as configured in the static settings
     * @return mixed|null
     */
    public static function getSiteName(){
        return self::getParam(NIGIRI_PARAM_SITE_NAME, '');
    }

    private static function getParamRecursive($name, $params, $default = null)
    {
        if (key_exists($name, $params)) {
            return $params[$name];
        } elseif (strpos($name, '.') !== false) {//Array Access
            $paramBoom = explode('.', $name);
            if (key_exists($paramBoom[0], $params) and is_array($params[$paramBoom[0]])) {
                return self::getParamRecursive(substr($name, strlen($paramBoom[0]) + 1), $params[$paramBoom[0]], $default);
            }
        }

        return $default;
    }

    public static function getAutoloader()
    {
        return self::$autoloader;
    }

    public static function printPage()
    {
        echo self::$theme->render();
    }

    private static function initDB($db)
    {
        if (!empty($db) and (($db instanceof DB) || is_array($db))) {
            if (is_array($db)) {
                if (class_exists($db['class'])) {
                    $class = new \ReflectionClass($db['class']);
                    if ($class->isSubclassOf('nigiri\db\DB')) {
                        self::$DB = $class->newInstance($db);
                    } else {
                        throw new Exception("Errore nella configurazione del database", 2,
                          "Il database specificato non estende la classe DB");
                    }
                } else {
                    throw new Exception("Errore nella configurazione del database", 3,
                      "La classe specificata per il database non esiste");
                }
            } else {
                self::$DB = $db;
            }
        }
    }

    private static function autoloadSetup($autoloader, $data)
    {
        self::$autoloader = $autoloader;

        foreach ($data as $prefix => $path) {
            self::$autoloader->addNamespace($prefix, $path);
        }
    }

    /**
     * Builds a Theme object from the theme config array
     * @param $config
     * @return ThemeInterface
     * @throws Exception
     */
    private static function buildTheme($config)
    {
        $reflected = self::themeClassCheck($config);

        if (!empty($config['config'])) {
            return $reflected->newInstanceArgs($config['config']);
        } else {
            return $reflected->newInstanceArgs();
        }
    }

    /**
     * Checks the
     * @param $config
     * @return \ReflectionClass
     * @throws Exception
     */
    private static function themeClassCheck($config)
    {
        try {
            $themeClass = new \ReflectionClass($config['class']);
            if (!$themeClass->isSubclassOf("\\nigiri\\themes\\ThemeInterface")) {
                throw new Exception("Errore configurazione tema per visualizzare il sito", 2,
                  "Il tema specificato non implementa l'interfaccia ThemeInterface");
            }

            return $themeClass;
        } catch (\ReflectionException $e) {
            throw new Exception("Errore configurazione tema per visualizzare il sito", 3,
              "Il tema specificato non esiste: " . $e->getMessage());
        }
    }
}
