<?php
/**
 * Created by PhpStorm.
 * User: mlively
 * Date: 8/13/16
 * Time: 5:23 PM
 */

namespace PhpProjects\AuthDev\Controllers;


use PhpProjects\AuthDev\Authentication\LoginService;
use PhpProjects\AuthDev\Views\ViewService;

trait PermissionableControllerTrait
{
    /**
     * @return ViewService
     */
    abstract protected function getViewService() : ViewService;

    /**
     * @return LoginService
     */
    protected function getLoginService() : LoginService
    {
        return LoginService::create();
    }


    /**
     * A helper function to ensure that the current session has $permission.
     *
     * If they are not authenticated, the user will be redirected to the login page and false returned.
     *
     * If they are authenticated but do not have access, they will be shown the nopermissions view and false returned.
     *
     * If they have permission no action will be taken and true will be returned.
     * @param string $permission
     * @return bool
     */
    protected function checkForPermission(string $permission) : bool
    {
        if (!$this->getLoginService()->isSessionAuthenticated())
        {
            $this->getViewService()->redirect('/auth/login?originalUrl=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
            return false;
        }
        elseif (!$this->getLoginService()->sessionHasPermission($permission))
        {
            $this->getViewService()->renderView('auth/nopermissions');
            return false;
        }
        return true;
    }


}