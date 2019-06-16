<?php

namespace Auth\Service;

use Auth\Adapter\DbTable as DbTableAuthAdapter;
use Auth\Adapter\SampleAdapter;
use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of AdapterFactory
 *
 * @author seyfer
 */
class AdapterFactory implements FactoryInterface
{

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return EntityManager
     */
    protected function getEM(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');

        return $entityManager;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this->create($serviceLocator, NULL);
    }

    /**
     * auth adapter based on current config
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $requestedName - for using in abstract factory
     * @return AuthenticationService
     * @throws \Exception
     */
    public function create(ServiceLocatorInterface $serviceLocator, $requestedName = NULL)
    {
        $config = $serviceLocator->get('config');
        $adapterName = $config['auth']['adapter'];
        $adapterConfig = $config['auth']['adapterConfig'][$adapterName];

        $authService = new AuthenticationService();

        switch ($adapterName) {
            case 'ZendDbAdapter':
                $adapter = $serviceLocator->get($adapterConfig['service']);

                $table = $adapterConfig['tableName'];
                $login = $adapterConfig['loginColumn'];
                $password = $adapterConfig['passwordColumn'];
                $method = $adapterConfig['method'];
                $dbTableAuthAdapter = new DbTableAuthAdapter($adapter, $table, $login, $password, $method);

                $authService->setAdapter($dbTableAuthAdapter);

                break;

            case 'SampleAdapter':
                /* @var $sampleAdapter SampleAdapter */
                $sampleAdapter = $serviceLocator->get($adapterConfig['service']);
                $sampleAdapter->setSite($adapterConfig['site']);
                $sampleAdapter->setSecretKey($adapterConfig['secretKey']);
                $sampleAdapter->setUrl($adapterConfig['url']);
                $authService->setAdapter($sampleAdapter);

                break;

            case 'DoctrineAdapter' :
                $authService = $serviceLocator->get('doctrine.authenticationservice.orm_default');

                break;

            default:
                throw new \Exception(__METHOD__ . ' no auth adapter configured');
                break;
        }

        $authService->setStorage($serviceLocator->get('Auth\Model\AuthStorage'));

        return $authService;
    }

}
