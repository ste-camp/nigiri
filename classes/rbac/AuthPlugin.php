<?php

namespace nigiri\rbac;

use nigiri\Controller;
use nigiri\exceptions\Exception;
use nigiri\exceptions\Forbidden;
use nigiri\plugins\PluginInterface;
use nigiri\Site;
use nigiri\themes\LayoutTheme;
use nigiri\views\Url;

class AuthPlugin implements PluginInterface
{

    const DENY = -1;
    const ALLOW = 1;

    private $config;

    /**
     * @var int|Role|Permission the default permission to apply when there is no specific policy defined for the current action
     */
    private $policy = null;

    public function __construct($config)
    {
        $this->config = $config;

        if (!empty($config['policy'])) {
            $this->policy = $this->policyEvaluation($config['policy']);
        } else {
            $this->policy = self::DENY;
        }
    }

    public function beforeAction($actionName)
    {
        $policy = $this->policy;
        $allow = true;
        if (!empty($this->config['rules'])) {
            $raw_p = null;
            $under = Controller::camelCaseToUnderscore($actionName);
            foreach ($this->config['rules'] as $rule) {
                if (in_array($under, $rule['actions'])) {
                    $raw_p = $rule;
                    break;
                }
            }

            if (!empty($raw_p)) {
                $allow = array_key_exists('allow', $raw_p) ? (boolean)$raw_p['allow'] : true;
                $policy = $this->policyEvaluation($raw_p['policy']);
            }
        }

        return $this->applyPolicy($policy, $allow);
    }

    public function afterAction($actionName, $actionOutput)
    {
        return $actionOutput;
    }

    /**
     * @param $p
     * @param bool $isAllow tells if the current policy is an Allow or a Deny one. Deny Policies trigger HTTP403 when a
     * Permission or Role matches correctly, Allow Policies trigger HTTP403 when none matches
     * @throws Forbidden
     */
    private function applyPolicy($p, $isAllow = true)
    {
        if ($p == self::ALLOW) {
            return true;
        } elseif ($p == self::DENY) {
            throw new Forbidden();
        } elseif (is_array($p)) {
            $needs_login = false;

            $match = false;
            foreach ($p as $temp) {
                if ($temp === Role::AUTHENTICATED_USER) {
                    $needs_login = true;
                    if (Site::getAuth()->isLoggedIn()) {
                        $match = true;
                        break;
                    }
                } elseif ($temp instanceof Permission) {
                    if (Site::getAuth()->iCan($temp)) {
                        $match = true;
                        break;
                    }
                } elseif ($temp instanceof Role) {
                    if (Site::getAuth()->userHasRole(Site::getAuth()->getLoggedInUser(), $temp)) {
                        $match = true;
                        break;
                    }
                }
            }

            if (($match && $isAllow) || (!$match && !$isAllow)) {
                return true;
            }

            if($needs_login){//If the page is not allow because authentication is required don't throw an error but redirect to login page
                Controller::redirectTo(Url::to(Site::getParam(NIGIRI_PARAM_LOGIN_URL), ['login_needed' => 1]));
                Site::switchTheme(new LayoutTheme());//back to standard theme, just to avoid rendering any strange layout which may require data we skipped initializing
                return false;
            }
            else {//If it's really not allowed here, throw real error
                throw new Forbidden();
            }
        }
    }

    private function policyEvaluation($p)
    {
        if (!empty($p)) {
            if ($p === self::ALLOW || $p === self::DENY) {
                return $p;
            } else {
                if (!is_array($p)) {
                    $p = [$p];
                }

                $out = [];
                foreach ($p as $temp) {
                    if ($temp === Role::AUTHENTICATED_USER) {
                        $out[] = Role::AUTHENTICATED_USER;
                    } elseif (is_string($temp)) {
                        try {
                            $out[] = new Permission($temp);
                        } catch (Exception $e) {//It's not a valid permission name
                            $r = Site::getAuth()->getRole($temp);
                            if (!empty($r)) {
                                $out[] = $r;
                            }
                        }
                    }
                }

                if (empty($out)) {
                    return self::DENY;
                } else {
                    return $out;
                }
            }
        } else {
            return self::DENY;
        }
    }
}
