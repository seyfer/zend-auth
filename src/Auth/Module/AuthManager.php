<?php

namespace Auth\Module;

use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;

/**
 * Description of AuthChecker
 *
 * @author seyfer
 */
class AuthManager
{

    protected $openedPathes = [];
    protected $closedPathes = [];
    protected $authEvenName = "dispatch";
    protected $aclEventName = "render";

    /**
     * configure and bind to dispatch event
     *
     * @param MvcEvent $e
     */
    public function bindAuthCheckClosure(MvcEvent $e)
    {
        $closure = $this->getAuthCheckClosure($e);

        $app = $e->getApplication();
        //стандартный роут слушатель
        $eventManager = $app->getEventManager();

        //событие проверки авторизации на роутах
        $eventManager->attach($this->authEvenName, $closure);

        //проверить открытый или закрытый раздел
        if (!$this->validate($e)) {
            return;
        }

        $this->initAcl($e);
    }

    /**
     *
     * @param MvcEvent $e
     * @return \Closure
     */
    public function getAuthCheckClosure(MvcEvent $e)
    {
        $closure = function (MvcEvent $e) {

            if (!$this->validate($e)) {
                return;
            }

            if (!$e->getApplication()->getServiceManager()
                ->get('Zend\Authentication\AuthenticationService')->hasIdentity()
            ) {

                $response = $this->formNotAuthResponse($e);
                $this->setBreakEvent($e, $response);
            }
        };

        return $closure;
    }

    /**
     *
     * @param MvcEvent $e
     * @return boolean
     */
    private function validate(MvcEvent $e)
    {
        //если роут логин, то не закрывать
        if ($this->checkOwnRouter($e)) {
            return FALSE;
        }

        //если консоль, то не закрывать
        if ($this->checkConsoleRequest($e)) {
            return FALSE;
        }

        //проверить открытый или закрытый раздел
        if ($this->checkAccessStrategy($e)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     *
     * @param MvcEvent $e
     */
    private function initAcl(MvcEvent $e)
    {
        $app = $e->getApplication();
        $serviceLocator = $app->getServiceManager();
        $config = $serviceLocator->get('config');
        $useAcl = $config['auth']['useAcl'];

        $authService = $app->getServiceManager()
            ->get('Zend\Authentication\AuthenticationService');

        $authCheck = $authService->hasIdentity();

        if ($authCheck) {
            $user = $authService->getIdentity();
        }

        if ($useAcl && $authCheck && $user->getIsActive() === 1) {

            $app = $e->getApplication();
            $eventManager = $app->getEventManager();

            $config = $serviceLocator->get('config');

            $acl = new \Auth\Module\AclManager();
            $acl->initAcl($e, $config['acl']);

            $eventManager->attach($this->aclEventName, [$acl, 'checkAcl']);
        }
    }

    /**
     *
     * @param MvcEvent $e
     * @return boolean
     * @throws \Exception
     */
    private function checkAccessStrategy(MvcEvent $e)
    {
        $app = $e->getApplication();
        $request = $app->getRequest();

        $currentUrl = $request->getUri()->getPath();
        $config = $app->getServiceManager()->get('config');
        $accessStrategy = $config['auth']['accessStrategy'];
        $accessStrategyConfig = $config['auth']['accessStrategyConfig'][$accessStrategy];

        switch ($accessStrategy) {
            case "AllOpenSomeClosed":
                $this->closedPathes = $accessStrategyConfig['closedPathes'];
                $closed = $this->checkClosedPathes($currentUrl);
                //если не закрыто - то не вешаем
                if (!$closed) {
                    return TRUE;
                }

                break;

            case "AllClosedSomeOpen" :
                $this->openedPathes = $accessStrategyConfig['openedPathes'];
                $excluded = $this->checkExcludedParts($currentUrl);
                if ($excluded) {
                    return TRUE;
                }

                break;

            default:
                throw new \Exception(__METHOD__ . " access strategy must be configured");
                break;
        }
    }

    /**
     *
     * @param MvcEvent $e
     * @return boolean
     */
    private function checkOwnRouter(MvcEvent $e)
    {
        $match = $e->getRouteMatch();
        if ($match && 0 === strpos($match->getMatchedRouteName(), 'login')) {
            return TRUE;
        }
    }

    /**
     *
     * @param MvcEvent $e
     * @return boolean
     */
    private function checkConsoleRequest(MvcEvent $e)
    {
        $app = $e->getApplication();
        $request = $app->getRequest();
        if ($request instanceof ConsoleRequest) {
            return TRUE;
        }
    }

    /**
     * AllOpenSomeClosed
     *
     * @param string $currentUrl
     * @return boolean
     */
    private function checkClosedPathes($currentUrl)
    {
        $closed = FALSE;
        foreach ($this->closedPathes as $closedPath) {
            //не найден
            if (FALSE === strpos($currentUrl, $closedPath)) {

            } else {
                $closed = TRUE;
            }
        }

        return $closed;
    }

    /**
     * AllClosedSomeOpen
     *
     * @param string $currentUrl
     * @return boolean
     */
    private function checkExcludedParts($currentUrl)
    {
        $excluded = FALSE;
        foreach ($this->openedPathes as $urlPart) {
            //не вешаем, если в пути есть exclude
            if (FALSE !== strpos($currentUrl, $urlPart)) {
                $excluded = TRUE;
            }
        }

        return $excluded;
    }

    /**
     * остановить обработку дальше
     *
     * @param MvcEvent $e
     * @param Response $response
     * @return Response
     */
    private function setBreakEvent(MvcEvent $e, Response $response)
    {
        // When an MvcEvent Listener returns a Response object,
        // It automatically short-circuit the Application running
        // -> true only for Route Event propagation see Zend\Mvc\Application::run
        // To avoid additional processing
        // we can attach a listener for Event Route with a high priority
        $stopCallBack = function ($e) use ($response) {
            $e->stopPropagation();

            return $response;
        };

        //Attach the "break" as a listener with a high priority
        $e->getApplication()
            ->getEventManager()
            ->attach(MvcEvent::EVENT_ROUTE, $stopCallBack, -10000);

        return $response;
    }

    /**
     * ответ об ошибке и редирект
     *
     * @param MvcEvent $e
     * @return Response
     */
    private function formNotAuthResponse(MvcEvent $e)
    {
        $url = $e->getRouter()
            ->assemble([], ['name' => 'login/login']);

        $response = $e->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();

        return $response;
    }

}
