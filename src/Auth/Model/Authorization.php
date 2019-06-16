<?php

namespace Auth\Model;

use Zend\Session\Container;

/**
 * Description of Authorization
 *
 * @author seyfer
 */
class Authorization
{

    private $authContainerName = "moduleAuth";

    const CONTAINER_NAME = "moduleAuth";

    /**
     *
     * @var Container
     */
    private $container;

    public function __construct()
    {
        $this->container = new Container($this->authContainerName);

        if (!$this->getSessionUser()) {
            self::redirectToAuth();
        }
    }

    public static function redirectToAuth()
    {
        header("Location:" . "/login");
    }

    public function getContractorId()
    {
        return $this->container->user->getContractor()['id'];
    }

    public function setSessionUser($user)
    {
        $this->container->user = $user;
    }

    public function getSessionUser()
    {
        return $this->container->user;
    }

}
