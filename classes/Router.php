<?php

namespace nigiri;

use nigiri\exceptions\FileNotFound;
use nigiri\exceptions\InternalServerError;

/**
 * Finds the pages to execute given the current url
 */
class Router
{
    private $pageUrl;
    private $controllerUrlComponent;
    private $controller;
    private $actionUrlComponent;
    private $action;
    private $language;

    /**
     * Router constructor.
     * @throws FileNotFound
     * @throws InternalServerError
     */
    public function __construct()
    {
        if (!empty($_GET['show_page'])) {
            $this->pageUrl = $_GET['show_page'];
        } else {
            $this->pageUrl = Site::getParam(NIGIRI_PARAM_DEFAULT_PAGE);
        }

        $boom = array_filter(explode('/', $this->pageUrl));

        $lang = Site::getParam(NIGIRI_PARAM_SUPPORTED_LANGUAGES, []);
        if (in_array($boom[0], $lang)) {
            $this->language = array_shift($boom);
            if (empty($boom)) {//Home page with a language specified
                $boom = array_filter(explode('/', Site::getParam(NIGIRI_PARAM_DEFAULT_PAGE)));
            }
        } else {
            $this->language = Site::getParam(NIGIRI_PARAM_DEFAULT_LANGUAGE);
        }

        if (count($boom) == 1) {
            $boom[1] = 'index';
        }

        if (count($boom) == 2) {
            $this->controllerUrlComponent = $boom[0];
            $this->controller = Controller::underscoreToCamelCase($this->controllerUrlComponent) . 'Controller';

            $this->actionUrlComponent = empty($boom[1]) ? 'index' : $boom[1];
            $this->action = Controller::underscoreToCamelCase($this->actionUrlComponent, false);
        } else {
            if (empty($this->pageUrl)) {
                throw new InternalServerError("Nessuna home page è stata definita");
            } else {
                throw new FileNotFound("", 'Impossibile trovare ' . $this->pageUrl);
            }
        }
    }

    /**
     * Calls the actual controller and method that should handle the request
     * @return string the HTML code to include in the output body
     * @throws FileNotFound
     * @throws \ReflectionException
     * @throws exceptions\Exception
     */
    public function routeRequest()
    {
        if (!class_exists('site\\controllers\\' . $this->controller)) {
            throw new FileNotFound();
        }

        ob_start();//Just a security measure to ensure accidental echos in the controllers don't break the theme output

        $class = new \ReflectionClass('site\\controllers\\' . $this->controller);
        if (!$class->isSubclassOf('nigiri\Controller')) {
            throw new FileNotFound();
        }

        /** @var Controller $instance */
        $instance = $class->newInstance();

        $action = null;
        $m = null;
        if ($class->hasMethod($this->action)) {
            $m = $class->getMethod($this->action);
        } elseif ($class->hasMethod('action' . ucfirst($this->action))) {
            $this->action = 'action' . ucfirst($this->action);
            $m = $class->getMethod($this->action);
        }

        if($m != null && $m->isPublic()){
            $action = $this->action;
        }

        if(empty($action)) {
            throw new FileNotFound();
        }

        //Enable the controller to have its own default theme
        $controller_theme = $instance->getDefaultControllerThemeConfig($action);
        if ($controller_theme != null) {
            Site::switchThemeByConfig($controller_theme);
        }

        $out = $instance->executeAction($action);
        ob_end_clean();

        return $out;
    }

    public function getPageUrl()
    {
        return $this->pageUrl;
    }

    /**
     * Get the name of the requested controller class
     * @return string
     */
    public function getControllerName()
    {
        return $this->controller;
    }

    public function getControllerUrlComponent()
    {
        return $this->controllerUrlComponent;
    }

    /**
     * Get the name of the requested action (without the eventual "action" prefix)
     * @return string
     */
    public function getActionName()
    {
        return $this->action;
    }

    public function getActionUrlComponent()
    {
        return $this->actionUrlComponent;
    }

    public function getRequestedLanguage()
    {
        return $this->language;
    }

    /**
     * Checks if a url points to the current page, given routing rules
     * @param string $page
     * @return bool
     */
    public function isCurrentPage($page)
    {
        if ($page == $this->pageUrl) {
            return true;
        }

        $boom = explode('/', $page);
        $lang = Site::getParam(NIGIRI_PARAM_SUPPORTED_LANGUAGES, []);
        if (in_array($boom[0], $lang)) {
            array_shift($boom);
        }

        if (count($boom) == 2) {
            return $boom[0] == Controller::camelCaseToUnderscore(substr($this->controller, 0,
                -10)) && $boom[1] == Controller::camelCaseToUnderscore($this->action);
        } elseif (count($boom) == 1) {
            return $boom[0] == Controller::camelCaseToUnderscore(substr($this->controller, 0,
                -10)) && Controller::camelCaseToUnderscore($this->action) == 'index';
        }

        return false;
    }
}
