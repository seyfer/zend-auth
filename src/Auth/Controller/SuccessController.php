<?php

namespace Auth\Controller;

use Zend\View\Model\ViewModel;

/**
 * Description of SuccessController
 *
 * @author seyfer
 */
class SuccessController extends BaseController
{

    public function indexAction()
    {
        if (!$this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute($this->defaultRedirectRoute);
        }

        return new ViewModel();
    }

}
