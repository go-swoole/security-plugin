<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 17:06
 */

namespace ESD\Plugins\Security;

use ESD\Plugins\Security\Beans\Principal;
use ESD\Plugins\Security\Helper\Tools;
use ESD\Plugins\Session\GetSession;
use ESD\Core\Server\Beans\Request;

trait GetSecurity
{
    use GetSession;

    public function getPrincipal(): ?Principal
    {
        return $this->getSession()->getAttribute("Principal");
    }

    public function setPrincipal(Principal $principal)
    {
        $this->getSession()->setAttribute("Principal", $principal);
    }

    /**
     * 当前账户有指定角色中的任意一个时返回true
     * @param string|array $roles
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        $principal = $this->getPrincipal();
        if ($principal instanceof Principal) {
            return $principal->hasAnyRole($roles);
        } else {
            return false;
        }
    }

    /**
     * 是否拥有权限
     * @param string|array $permissions
     * @return bool
     */
    public function hasAnyPermission($permissions)
    {
        $principal = $this->getPrincipal();
        if ($principal instanceof Principal) {
            return $principal->hasAnyPermission($permissions);
        } else {
            return false;
        }
    }

    /**
     * 是否已经登录
     * @return bool
     */
    public function isAuthenticated()
    {
        $principal = $this->getPrincipal();
        if ($principal == null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * IP地址是否符合支持10.0.0.0/16这种
     * @param array $ips
     * @return bool
     */
    public function hasIpAddress($ips)
    {
        $request = getDeepContextValueByClassName(Request::class);
        if ($request instanceof Request) {
            $ip = $request->getServer(Request::SERVER_REMOTE_ADDR);
            if (is_array($ips)) {
                foreach ($ips as $oneip) {
                    if ($oneip == $ip) return true;
                    $exip = explode("/", $oneip);
                    $mask = $exip[1] ?? null;
                    if ($mask != null) {
                        if (Tools::netMatch($ip, $exip[0], $mask)) return true;
                    }
                }
                return false;
            } elseif (is_string($ips)) {
                if ($ips == $ip) return true;
                $exip = explode("/", $ips);
                $mask = $exip[1] ?? null;
                if ($mask != null) {
                    return Tools::netMatch($ip, $exip[0], $mask);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}