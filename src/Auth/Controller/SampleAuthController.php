<?php

namespace Auth\Controller;

use Zend\Http\Response;
use Zend\Session\Container;

/**
 * Description of SampleAuthController
 *
 * @author seyfer
 */
class SampleAuthController extends AuthController
{

    protected $warningContainerName = "warningAuth";

    /**
     * в случае успеха - редирект
     */
    protected function redirectToSuccess()
    {
        $adapter = $this->getAuthService()->getAdapter();
        $adapter->clearAvailableContracts();

        //check referer first
        $containerReferer = new Container('authReferer');

        if ($containerReferer->refererUri) {
            return $this->redirect()->toUrl($containerReferer->refererUri);
        } else {
            return $this->redirect()->toRoute($this->successRouteRedirect);
        }
    }

    /**
     * форма авторизации
     *
     * @return array
     */
    public function loginAction()
    {
        //if already login, redirect to success page
        if ($this->getAuthService()->hasIdentity()) {
            $this->redirectToSuccess();
        }

        $form = $this->getForm();

        $contracts = [];
        if ($this->params()->fromQuery("warning")) {

            //заполнить ввод после перехода
            $container = new Container($this->warningContainerName);
            $data = $container->post;
            $form->setData($data);

            $adapter = $this->getAuthService()->getAdapter();
            $contracts = $adapter->getAvailableContracts();
        }

        return [
            'form' => $form,
            'contracts' => $contracts,
            'messages' => $this->flashmessenger()->getMessages(),
        ];
    }

    /**
     *
     * @return Response
     */
    public function authenticateAction()
    {
        $this->flashmessenger()->clearCurrentMessages();

        $this->validateRequest();

        $this->validateForm();

        try {

            $post = $this->getRequest()->getPost();

            $this->getAuthService()->getAdapter()
                ->setIdentity($post['username'])
                ->setCredential($post['password'])
                ->setContractId($post['contracts']);

            $result = $this->getAuthService()->authenticate();

            if ($result->isValid()) {
                return $this->processValidAuthResult($result, $post);
            } else {
                return $this->processInvalidAuthResult($result, $post);
            }
        } catch (\Exception $e) {
            $this->flashmessenger()->addErrorMessage($e->getMessage());
        }

        return $this->redirect()->toRoute($this->defaultRedirectRoute);
    }

    /**
     *
     * @param \Zend\Authentication\Result $result
     * @param array $post
     * @return Response
     */
    protected function processInvalidAuthResult(\Zend\Authentication\Result $result, $post)
    {
        foreach ($result->getMessages() as $message) {
            $this->flashmessenger()->addErrorMessage($message);
        }

        $adapter = $this->getAuthService()->getAdapter();
        $code = $adapter->getStatus();

        if ($code == \Auth\Adapter\SampleAdapter::STATUS_WARNING) {

            //запомнить ввод иначе пропадет
            $container = new Container($this->warningContainerName);
            $container->post = $post;

            return $this->redirect()->toRoute($this->defaultRedirectRoute, [], [
                "query" => [
                    "warning" => "1",
                ],
            ]);
        }

        return $this->redirect()->toRoute($this->defaultRedirectRoute);
    }

}
