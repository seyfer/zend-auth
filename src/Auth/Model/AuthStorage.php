<?php

namespace Auth\Model;

use Zend\Authentication\Storage;

/**
 * Description of AuthStorage
 *
 * @author seyfer
 */
class AuthStorage extends Storage\Session
{

    /**
     * minutes by default
     *
     * @var
     */
    private $expTime;

    public function __construct($namespace = null, $member = null, \Zend\Session\ManagerInterface $manager = null)
    {
        parent::__construct($namespace, $member, $manager);

        //default 1 hour
        $this->expTime = 3600;
    }

    public function setRememberMe($time = 0)
    {
        $expTime = $time ? $time : $this->expTime;

        $this->session->getManager()->rememberMe($expTime);
    }

    public function forgetMe()
    {
        $this->session->getManager()->forgetMe();
    }

}
