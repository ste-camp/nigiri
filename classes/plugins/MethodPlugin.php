<?php

namespace nigiri\plugins;

use nigiri\Controller;
use nigiri\exceptions\BadRequest;

class MethodPlugin implements PluginInterface
{

    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function beforeAction($actionName)
    {
        $action = $actionName;
        if (strpos($actionName, 'action') === 0) {
            $action = substr($actionName, 6);
        }
        $action = Controller::camelCaseToUnderscore($action);

        if (array_key_exists($action, $this->config) || array_key_exists($actionName, $this->config)) {
            $methods = array_key_exists($action, $this->config) ? $this->config[$action] : $this->config[$actionName];
            if (!is_array($methods)) {
                $methods = [$methods];
            }

            $found = false;
            foreach ($methods as $m) {
                if (strtoupper($m) == strtoupper($_SERVER['REQUEST_METHOD'])) {
                    $found = true;
                }
            }

            if (!$found) {
                throw new BadRequest(l("This page is not accessible with a %s request", strtoupper($_SERVER['REQUEST_METHOD'])));
            }
        }
    }

    public function afterAction($actionName, $actionOutput)
    {
        return $actionOutput;
    }
}
