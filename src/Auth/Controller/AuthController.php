<?php

namespace Auth\Controller;

use Auth\Form\Filter\LoginFilter;
use Auth\Form\LoginForm;
use Zend\Form\Form;
use Zend\Http\Response;
use Zend\Session\Container;

/**
 * Description of AuthController
 *
 * @author seyfer
 */
class AuthController extends BaseController
{

    protected $form;
    protected $successRouteRedirect = "success";

    /**
     * получить форму с аннотаций
     *
     * @return Form
     */
    public function getForm()
    {
        if (!$this->form) {
            $this->form = new LoginForm();
            $this->form->setInputFilter(new LoginFilter());
        }

        return $this->form;
    }

    /**
     * в случае успеха - редирект
     */
    protected function redirectToSuccess()
    {
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

        return [
            'form' => $form,
            'messages' => $this->flashmessenger()->getMessages(),
        ];
    }

    /**
     * Here is main auth processing
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
                ->setCredential($post['password']);

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
     */
    protected function processValidAuthResult(\Zend\Authentication\Result $result, $post)
    {
        //check if it has rememberMe :
        if ($post['rememberme'] == 1) {

            $storage = $this->getSessionStorage();
            $storage->setRememberMe();

            //set storage again
            $this->getAuthService()
                ->setStorage($storage);
        }

        $authManager = new \Auth\Model\Authorization();
        $user = $authManager->getSessionUser();

        $this->getAuthService()
            ->getStorage()->write($user);

        return $this->redirectToSuccess();
    }

    /**
     *
     * @param \Zend\Authentication\Result $result
     * @param array $post
     */
    protected function processInvalidAuthResult(\Zend\Authentication\Result $result, $post)
    {
        foreach ($result->getMessages() as $message) {
            $this->flashmessenger()->addErrorMessage($message);
        }

        return $this->redirect()->toRoute($this->defaultRedirectRoute);
    }

    /**
     * check data passed to form
     */
    protected function validateForm()
    {
        $form = $this->getForm();
        $post = $this->getRequest()->getPost();
        $form->setData($post);

        if (!$form->isValid()) {
            $this->flashmessenger()->addErrorMessage(serialize($form->getMessages()));
            $this->redirect()->toRoute($this->defaultRedirectRoute);
        }
    }

    /**
     *
     * @return Response
     */
    protected function validateRequest()
    {
        $request = $this->getRequest();
        if (!$request->isPost() || !$request->getPost()['username'] || !$request->getPost()['password']) {
            return $this->redirect()->toRoute($this->defaultRedirectRoute);
        }
    }

    /**
     *
     * @return Response
     */
    public function logoutAction()
    {
        $this->getSessionStorage()->forgetMe();
        $this->getAuthService()->clearIdentity();
        $this->getSessionManager()->destroy();

        $this->flashmessenger()->addMessage("Вы вышли из системы");

        return $this->redirect()->toRoute($this->defaultRedirectRoute);
    }

}
