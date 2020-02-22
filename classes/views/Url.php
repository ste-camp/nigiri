<?php

namespace nigiri\views;

use nigiri\Controller;
use nigiri\Site;

/**
 * Utility class that helps generating correct URLs for pages and resources of the site
 */
class Url
{
    /**
     * Creates a URL to a page of the website.
     * Takes into account usage of CLEAN_URLS and URL_PREFIX
     * @param string $l_page the name of the page
     * @param string $query the GET query. Can be a string or an array of key value pairs
     * @param bool $absolute generates an absolute URL instead of one relative to the document root
     * @param string $language
     * @return string the URL linking to the specified page
     */
    public static function to($l_page = '', $query = '', $absolute = false, $language = '')
    {
        if (empty($l_page)) {
            $url = Site::getRouter()->getPageUrl();

            if (!empty($language)) {
                $boom = explode('/', $url);
                $lang = Site::getParam("languages", []);
                if (in_array($boom[0], $lang)) {//If the first argument is a language code
                    array_shift($boom);
                    $url = $language . '/' . implode('/', $boom);
                } else {
                    $url = $language . '/' . implode('/', $boom);
                }
            }
        } else {
            $url = $l_page;

            $avail_lang = Site::getParam("languages", []);
            if (count($avail_lang) > 1) {
                $language = empty($language) ? Site::getRouter()->getRequestedLanguage() : $language;
            } else {//No need to specify a language if this is not really a multilanguage site
                $language = '';
            }

            if ($url != '/') {
                $boom = explode('/', $url);
                if (in_array($boom[0], $avail_lang)) {
                    if (!empty($language)) {
                        array_shift($boom);
                        $url = $language . '/' . implode('/', $boom);
                    }
                } else {
                    $url = $language . '/' . implode('/', $boom);
                }
            } else {
                $url = $language . '/';
            }
        }

        if (!Site::getParam('clean_urls')) {
            $url = 'index.php?show_page=' . $url;
        }

        return self::make($url, $query, $absolute);
    }

    /**
     * Method to generate a URL by specifying the name of the action and controller the URL should invoke
     * @param string $action name of the action to invoke (can be without the initial 'action' prefix). If empty, the current one will be used
     * @param string $controller name of the controller to invoke (can be without the 'Controller' suffix. If empty, the current one will be used
     * @param string $query the GET query. Can be a string or an array of key value pairs
     * @param bool $absolute generates an absolute URL instead of one relative to the document root
     * @param string $language
     * @return string the URL invoking the Controller and Action specified
     */
    public static function toAction($action = '', $controller = '', $query = '', $absolute = false, $language = '')
    {
        if (empty($action)) {
            $action = Site::getRouter()->getActionName();
        }
        if (empty($controller)) {
            $controller = Site::getRouter()->getControllerName();
        }

        $action = Controller::camelCaseToUnderscore($action);
        $controller[0] = strtolower($controller[0]);
        if (strrpos($controller, "Controller") === strlen($controller) - 10) {//if it ends with "Controller"
            $controller = substr($controller, 0, -10);
        }
        $controller = Controller::camelCaseToUnderscore($controller);

        return self::to($controller . '/' . $action, $query, $absolute, $language);
    }

    /**
     * Low level function to make urls of this website
     * @param string $path : actual path from the index.php page to the resource
     * @param string $query : the GET query. Can be a string or an array of key value pairs
     * @param bool $absolute : generates an absolute URL instead of one relative to the document root
     * @return string the requested URL
     */
    private static function make($path, $query = '', $absolute = false)
    {
        $url = $path;

        if (!empty($query)) {
            if (is_array($query)) {
                $temp = array();
                foreach ($query as $k => $v) {
                    $temp[] = $k . '=' . urlencode($v);
                }
                $query = implode('&', $temp);
            }

            if (strpos($url, '?') === false) {
                $url .= '?' . $query;
            } else {
                $url .= '&' . $query;
            }
        }

        if ($url[0] == '/') {
            $url = substr($url, 1);
        }

        if (Site::getParam('url_prefix') != '') {
            $pre = Site::getParam('url_prefix');
            if ($pre[0] != '/') {
                $pre = '/' . $pre;
            }
            $url = $pre . $url;
        } else {
            $url = '/' . $url;
        }

        if ($absolute) {
            $url = (empty($_SERVER["REQUEST_SCHEME"]) ? 'http' : $_SERVER["REQUEST_SCHEME"]) . '://' . $_SERVER["HTTP_HOST"] . $url;
        }

        return $url;
    }

    /**
     * Function to make urls of resources in the website (e.g. images, css, js)
     * @param string $path
     * @param bool $absolute
     * @param string $query
     * @return string the URL to the resource
     */
    public static function resource($path, $absolute = false, $query = '')
    {
        return self::make($path, $query, $absolute);
    }
}
