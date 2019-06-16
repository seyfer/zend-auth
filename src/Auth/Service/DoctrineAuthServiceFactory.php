<?php

namespace Auth\Service;

use DoctrineModule\Service\Authentication\AuthenticationServiceFactory;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of DoctrineAuthServiceFactory
 *
 * @author seyfer
 */
class DoctrineAuthServiceFactory extends AuthenticationServiceFactory
{

    public function __construct($name = "orm_default")
    {
        $this->name = $name;
    }

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Zend\Authentication\AuthenticationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $adapter = $serviceLocator->get('doctrine.authenticationadapter.' . $this->getName());

        $authService = new AuthenticationService(
            $serviceLocator->get('doctrine.authenticationstorage.' . $this->getName()), $adapter);

        return $authService;
    }

}
