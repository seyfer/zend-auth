<?php

namespace Auth\Service;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of ControllerFactory
 *
 * @author seyfer
 */
class AuthControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this->create($serviceLocator, NULL);
    }

    /**
     * auth adapter based on current config
     *
     * @param ControllerManager $controllerManager
     * @param string $requestedName - for using in abstract factory
     * @return AbstractActionController
     * @throws \Exception
     */
    public function create(ControllerManager $controllerManager, $requestedName = NULL)
    {
        $config = $controllerManager->getServiceLocator()->get('config');
        $adapterName = $config['auth']['adapter'];

        switch ($adapterName) {
            case 'ZendDbAdapter':

                $controller = new \Auth\Controller\AuthController();
                break;

            case 'SampleAdapter':

                $controller = new \Auth\Controller\SampleAuthController();
                break;

            case 'DoctrineAdapter':
                $controller = new \Auth\Controller\AuthController();
                break;

            default:
                throw new \Exception(__METHOD__ . ' no auth adapter configured');
                break;
        }

        return $controller;
    }

}
