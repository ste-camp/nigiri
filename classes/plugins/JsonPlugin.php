<?php

namespace nigiri\plugins;

use nigiri\Controller;

class JsonPlugin implements PluginInterface
{
    public const CONFIG_ALL_PAGES = '*';

    private $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function beforeAction($actionName)
    {
    }

    public function afterAction($actionName, $actionOutput)
    {
        $underscore_action = Controller::camelCaseToUnderscore($actionName);

        if (in_array($actionName, $this->config) or in_array($underscore_action, $this->config) or in_array(self::CONFIG_ALL_PAGES, $this->config)) {
            header('Content-Type: application/json; charset=utf-8');

            return json_encode($actionOutput);
        } else {
            return $actionOutput;
        }
    }
}
