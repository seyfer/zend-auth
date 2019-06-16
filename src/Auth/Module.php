<?php

namespace Auth;

use Auth\Adapter\SampleAdapter;
use Zend\Console\Request as ConsoleRequest;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

/**
 * Description of Module
 *
 * @author seyfer
 */
class Module implements
    AutoloaderProviderInterface, DependencyIndicatorInterface, ServiceProviderInterface, ControllerProviderInterface
{

    public function onBootstrap(MvcEvent $e)
    {
        //поднять сессию
        $this->bootstrapSession($e);

        //сохранить ссылку с которой пришли для редиректа
        $this->saveRefererUrl($e);

        //событие проверки авторизации на роутах
        $this->checkAuth($e);
    }

    /**
     *
     * @param MvcEvent $e
     */
    private function checkAuth(MvcEvent $e)
    {
        $authChecker = new Module\AuthManager();
        $authChecker->bindAuthCheckClosure($e);
    }

    /**
     * save referer for redirection after auth
     *
     * @param MvcEvent $e
     */
    private function saveRefererUrl(MvcEvent $e)
    {
        $app = $e->getApplication();
        $request = $app->getRequest();
        if (!$app->getServiceManager()
                ->get('Zend\Authentication\AuthenticationService')
                ->hasIdentity() &&
            !$request instanceof ConsoleRequest
        ) {

            $requestedUri = $request->getUri()->getPath();

            $containerReferer = new Container('authReferer');
            if (FALSE === strpos($requestedUri, 'auth')) {
                $containerReferer->refererUri = $requestedUri;
            }
        }
    }

    /**
     * создать сессию при старте
     *
     * @param \Zend\Mvc\MvcEvent $e
     */
    private function bootstrapSession(MvcEvent $e)
    {
        $session = $e->getApplication()
            ->getServiceManager()
            ->get(\Zend\Session\SessionManager::class);
        $session->start();

        $container = new Container('initialized');
        if (!isset($container->init)) {
            $session->regenerateId(true);
            $container->init = 1;
        }
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'Auth\Adapter\SampleAdapter' => function (ServiceLocatorInterface $sm) {
                    return new SampleAdapter();
                },
                'Auth\Model\AuthStorage' => function (ServiceLocatorInterface $sm) {
                    return new \Auth\Model\AuthStorage('auth_storage');
                },
                'Zend\Authentication\AuthenticationService' => Service\AdapterFactory::class,
                //инициализация менеджера сессии
                'Zend\Session\SessionManager' => new \Auth\Session\Service\AppSessionManagerFactory(),
                'doctrine.authenticationservice.orm_default' => \Auth\Service\DoctrineAuthServiceFactory::class,
                'doctrine.authenticationadapter.orm_default' => Service\DoctrineAdapterFactory::class,
            ],
            'abstract_factories' => [
            ],
        ];
    }

    public function getModuleDependencies()
    {
        return [
            'ZendBaseModel',
            'Sender',
            'DoctrineModule',
            'DoctrineORMModule',
        ];
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/../../autoload_classmap.php',
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__,
                ],
            ],
        ];
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * @param MvcEvent $e
     * @throws \Exception
     * @deprecated
     */
    private function checkSenderModule(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $manager = $sm->get('ModuleManager');
        $modules = $manager->getLoadedModules();
        if (!array_key_exists("Sender", $modules) && class_exists('SampleAdapter')) {
            throw new \Exception(__METHOD__ . " need Sender module");
        }
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                'Auth\Controller\Auth' => Service\AuthControllerFactory::class,
            ],
        ];
    }

}
