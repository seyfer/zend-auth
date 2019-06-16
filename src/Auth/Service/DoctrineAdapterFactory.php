<?php

namespace Auth\Service;

use Auth\Adapter\DoctrineAdapter;
use Auth\Entity\User;
use DoctrineModule\Service\Authentication\AdapterFactory as DoctrineAuthAdapterFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of DoctrineAdapterFactory
 *
 * @author seyfer
 */
class DoctrineAdapterFactory extends DoctrineAuthAdapterFactory
{

    public function __construct($name = "orm_default")
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     *
     * @return \DoctrineModule\Authentication\Adapter\ObjectRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $options \DoctrineModule\Options\Authentication */
        $options = $this->getOptions($serviceLocator, 'authentication');

        $options->setCredentialCallable(function (User $user, $passwordGiven) {
            return $user->getPassword() === md5($passwordGiven);
        });

        if (is_string($objectManager = $options->getObjectManager())) {
            $options->setObjectManager($serviceLocator->get($objectManager));
        }

        return new DoctrineAdapter($options);
    }

}
