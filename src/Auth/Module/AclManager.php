<?php

namespace Auth\Module;

use Auth\Model\Authorization;
use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Session\Container;

class AclManager
{

    /**
     *
     * @var ZendAcl
     */
    private $accessList;

    public function getAccessList()
    {
        return $this->accessList;
    }

    public function setAccessList(ZendAcl $accessList)
    {
        $this->accessList = $accessList;

        return $this;
    }

    /**
     *
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function initAcl(MvcEvent $e, $config)
    {

        $acl = new ZendAcl();
        $roles = $config;
        $allResources = [];
        foreach ($roles as $role => $resources) {

            $role = new Role($role);
            $acl->addRole($role);

            $allResources = array_merge($resources, $allResources);

            //adding resources
            foreach ($resources as $resource) {
                if (!$acl->hasResource($resource)) {
                    $acl->addResource(new Resource($resource));
                }
            }
            //adding restrictions
            foreach ($allResources as $resource) {
                $acl->allow($role, $resource);
            }
        }

        $this->accessList = $acl;

        //setting to view
        $e->getViewModel()->acl = $acl;
    }

    /**
     *
     * @param \Zend\Mvc\MvcEvent $e
     * @return void
     */
    public function checkAcl(MvcEvent $e)
    {
        $route = $e->getRouteMatch()->getMatchedRouteName();
        $request = $e->getRequest();

        //not check on console request
        if ($request instanceof ConsoleRequest) {
            return;
        }

        $authCont = new Container(Authorization::CONTAINER_NAME);

        //you set your role from auth
        if ($authCont->user) {
            $userRole = $authCont->user->getRole()->getName();
        } else {
            $userRole = 'guest';
        }

        if (!$e->getViewModel()->acl->isAllowed($userRole, $route)) {
            $this->formNotAuthResponse($e);
        }
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
