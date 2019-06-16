<?php

namespace Auth\Controller;

use Auth\Model\AuthStorage;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;

/**
 * Description of BaseController
 *
 * @author seyfer
 */
class BaseController extends AbstractActionController
{

    protected $storage;
    protected $authservice;
    protected $sessionManager;
    protected $defaultRedirectRoute = "login";

    /**
     *
     * @return AuthenticationService
     */
    public function getAuthService()
    {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()
                ->get('Zend\Authentication\AuthenticationService');
        }

        return $this->authservice;
    }

    /**
     *
     * @return AuthStorage
     */
    public function getSessionStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()
                ->get(AuthStorage::class);
        }

        return $this->storage;
    }

    /**
     *
     * @return SessionManager
     */
    public function getSessionManager()
    {
        if (!$this->sessionManager) {
            $this->sessionManager = $this->getServiceLocator()
                ->get(SessionManager::class);
        }

        return $this->sessionManager;
    }

}
