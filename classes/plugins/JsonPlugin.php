<?php

namespace nigiri\plugins;

use nigiri\Controller;
use nigiri\Site;
use nigiri\themes\AjaxTheme;

class JsonPlugin implements PluginInterface
{
    const CONFIG_ALL_PAGES = '*';

    private $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function beforeAction($actionName)
    {
        $underscore_action = Controller::camelCaseToUnderscore($actionName);

        if (in_array($actionName, $this->config) or in_array($underscore_action, $this->config) or in_array(self::CONFIG_ALL_PAGES, $this->config)) {
            Site::switchTheme(new AjaxTheme());
            header('Content-Type: application/json; charset=utf-8');
        }
    }

    public function afterAction($actionName, $actionOutput)
    {
        $underscore_action = Controller::camelCaseToUnderscore($actionName);

        if (in_array($actionName, $this->config) or in_array($underscore_action, $this->config) or in_array(self::CONFIG_ALL_PAGES, $this->config)) {
            return json_encode($actionOutput);
        } else {
            return $actionOutput;
        }
    }
}
