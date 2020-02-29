<?php

namespace nigiri\plugins;

interface PluginInterface
{
    public function __construct($config);

    /**
     * Code to execute before the controller action is actually called
     * It can return anything (even nothing) if the action should be executed. If it returns false (=== false) then the controller action is NOT executed
     * @param $actionName
     * @return void | false
     */
    public function beforeAction($actionName);

    /**
     * Code to execute after a controller action is called, before the output gets passed to the theme and layout
     * This callback is not called if the action is not called for the effect of beforeAction()
     * @param $actionName
     * @param $actionOutput
     * @return mixed
     */
    public function afterAction($actionName, $actionOutput);
}
