<?php

namespace Auth\Adapter;

use Auth\Model\Authorization;
use DoctrineModule\Authentication\Adapter\ObjectRepository;

/**
 * Description of DoctrineAdapter
 *
 * @author seyfer
 */
class DoctrineAdapter extends ObjectRepository
{

    public function authenticate()
    {
        $authResult = parent::authenticate();

        $auth = new Authorization();
        $auth->setSessionUser($authResult->getIdentity());

        return $authResult;
    }

}
