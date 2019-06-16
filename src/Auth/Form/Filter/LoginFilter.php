<?php

namespace Auth\Form\Filter;

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Description of LoginFilter
 *
 * @author seyfer
 */
class LoginFilter extends InputFilter
{

    public function __construct()
    {
        $this->addElements();
    }

    protected function addElements()
    {
        $username = new Input('username');
        $username->setRequired(TRUE);
        $this->add($username);

        $password = new Input('password');
        $password->setRequired(TRUE);
        $this->add($password);

        $email = new Input('email');
        $email->setRequired(FALSE);
        $this->add($email);

        $rememberme = new Input('rememberme');
        $rememberme->setRequired(FALSE);
        $this->add($rememberme);

        $submit = new Input('submit');
        $submit->setRequired(FALSE);
        $this->add($submit);
    }

}
