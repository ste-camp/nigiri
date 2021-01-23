<?php

namespace nigiri;

use nigiri\exceptions\Exception;
use nigiri\views\Html;
use nigiri\views\Url;

/**
 * Email Sender, wrapper around PHPMailer
 */
class Email
{
    /**
     * @var \PHPMailer
     */
    private $mail;

    public function __construct()
    {
        require_once(dirname(__DIR__) . '/libraries/PHPMailer/class.phpmailer.php');
        $this->mail = new \PHPMailer();
        $this->mail->PluginDir = dirname(__DIR__) . '/libraries/PHPMailer/';
        $this->mail->CharSet = 'utf-8';
    }

    /**
     * Invia un'email in UTF-8 (con Allegato)
     * @param string $subject : oggetto dell'email
     * @param string $message : il messaggio da inviare, può anche essere il percorso di un file
     * @param bool $html : indica se $message è in html
     * @param array $data : i dati da sostituire nel testo dell'email, le chiavi dell'array saranno richiamabili nel testo come @chiave. I dati nella chiave php_data vengono invece passati come variabili php se $message è un file
     * @param array $from : opzionale. un array con le informazioni del mittente nelle chiavi 'name' e 'addr'
     * @param array $header : opzionale. header addizionali da inviare. Il nome passato come chiave nell'array, il valore nel valore
     * @return bool true se l'invio è andato a buon fine. False altrimenti
     */
    public function send(
      $subject,
      $message,
      $html = false,
      $data = array(),
      $from = array(),
      $header = null
    ) {
        require_once(dirname(__DIR__) . '/libraries/PHPMailer/class.phpmailer.php');

        try {
            //Invio con SMTP
            if (Site::getParam(NIGIRI_PARAM_EMAIL_SMTP, false)) {
                $this->mail->IsSMTP();
                $this->mail->Host = Site::getParam(NIGIRI_PARAM_EMAIL_SMTP_HOST, '');
                $this->mail->Port = Site::getParam(NIGIRI_PARAM_EMAIL_SMTP_PORT, '25');
                $user = Site::getParam(NIGIRI_PARAM_EMAIL_SMTP_USER, '');
                if (!empty($user)) {
                    $this->mail->SMTPAuth = true;
                    $this->mail->Username = $user;
                    $this->mail->Password = Site::getParam(NIGIRI_PARAM_EMAIL_SMTP_PASSWORD, '');
                }
                $this->mail->SMTPSecure = Site::getParam(NIGIRI_PARAM_EMAIL_SMTP_SECURE);
            }

            if (empty($from) || empty($from['addr'])) {
                $this->mail->SetFrom(Site::getParam(NIGIRI_PARAM_EMAIL), Site::getParam(NIGIRI_PARAM_SITE_NAME), true);
            } else {
                if (!isset($from['name'])) {
                    $from['name'] = '';
                }
                $this->mail->SetFrom($from['addr'], $from['name'], true);
                $this->mail->Sender = Site::getParam(NIGIRI_PARAM_EMAIL);
            }

            if ($html) {
                $this->mail->IsHTML();
            }

            $this->mail->Subject = $subject;

            $search_paths = [
                dirname(__DIR__) . '/email/' . $message . '.php',
                dirname(__DIR__) . '/email/' . $message,
                $message
            ];
            foreach($search_paths as $path){
                if (file_exists($path)) {
                    $message = page_include($path,
                      array_merge(
                        ['email' => $this->mail, 'site_name' => Site::getSiteName(), 'site_url' => Url::to('/', '', true)],
                        !empty($data['php_data']) ? $data['php_data'] : array()));
                    break;
                }
            }

            if ($html && empty($data['no_layout'])) {
                $message = page_include(dirname(__DIR__) . '/email/layout.php', [
                  'message' => $message,
                  'subject' => $subject,
                  'email' => $this->mail
                ]);
            }

            if (isset($data['php_data'])) {
                unset($data['php_data']);
            }

            $this->mail->Body = $this->tokens_substitution($message, $data, $html);

            if (!empty($header)) {
                foreach ($header as $key => $value) {
                    $this->mail->AddCustomHeader($key, $value);
                }
            }

            $ret = $this->mail->Send();
            if (!$ret) {
                (new Exception("Errore invio email", 0, "send method: " . $this->mail->ErrorInfo))->logErrorToDb();

                return false;
            }
        } catch (\phpmailerException $e) {
            (new Exception("Errore invio email", 0, "eccezione", $e))->logErrorToDb();

            return false;
        }

        return true;
    }

    public function addRecipients($to)
    {
        if (is_array($to)) {
            if ($this->is_email_array($to)) {
                $to['to'][] = $to;
            }
            $enter = false;
            if (isset($to['to'])) {
                $this->mail_recipient_adding($to, 'to', 'AddAddress');
                $enter = true;
            }
            if (isset($to['cc'])) {
                $this->mail_recipient_adding($to, 'cc', 'AddCC');
                $enter = true;
            }
            if (isset($to['bcc'])) {
                $this->mail_recipient_adding($to, 'bcc', 'AddBCC');
                $enter = true;
            }
            if (!$enter) {//Last Resort. It is just an array of strings, each one email address
                $this->mail_recipient_adding(array('to' => $to), 'to', 'AddAddress');
            }
        } else {
            $this->mail->AddAddress($to);
        }

        return $this;
    }

    public function addAttachment($path, $name = '')
    {
        $this->mail->AddAttachment($path, $name);
    }

    private function is_email_array($arr)
    {
        if (isset($arr['addr'])) {
            return true;
        }

        return false;
    }

    private function mail_recipient_adding($arr, $key, $met)
    {
        if ($this->is_email_array($arr[$key])) {
            $arr[$key][] = $arr[$key];
            unset($arr[$key]['addr']);
            unset($arr[$key]['name']);
        }
        foreach ($arr[$key] as $recipient) {
            if (is_string($recipient)) {
                $this->mail->$met($recipient);
            } else {
                if (!empty($recipient['addr'])) {
                    if (!isset($recipient['name'])) {
                        $recipient['name'] = '';
                    }
                    $this->mail->$met($recipient['addr'], $recipient['name']);
                }
            }
        }
    }

    private function tokens_substitution($str, $tokens, $escape_html = false)
    {
        $patterns = [
          '/@site_name\b/s',
          '/@site_url\b/s',
          '/@year\b/s'
        ];
        $values = [
          Site::getParam(NIGIRI_PARAM_SITE_NAME),
          Url::to('/', [], true),
          date('Y')
        ];
        foreach ($tokens as $k => $v) {
            $patterns[] = '/@' . preg_quote($k, '/') . '\b/s';
            if ($escape_html) {
                $values[] = Html::escape($v);
            } else {
                $values[] = $v;
            }
        }

        return preg_replace($patterns, $values, $str);
    }
}
