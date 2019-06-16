<?php

namespace Auth\Model;

use Zend\Crypt\Exception;
use Zend\Crypt\Symmetric\Mcrypt;

/**
 * Description of SampleMcrypt
 *
 * @author seyfer
 */
class SampleMcrypt extends Mcrypt
{

    protected $salt;

    public function __construct($options = [])
    {
        /**
         * так настроен протокол
         */
        $options = [
            'algo' => MCRYPT_3DES,
            'mode' => MCRYPT_MODE_CBC,
        ];

        parent::__construct($options);
    }

    /**
     * соль в этом алгоритме
     *
     * @return string
     */
    public function calcSalt()
    {
        $this->salt = mcrypt_create_iv($this->getSaltSize(), MCRYPT_DEV_URANDOM);

        return $this->salt;
    }

    /**
     * соль
     */
    public function setCalculatedSalt()
    {
        $this->setSalt($this->calcSalt());
    }

    public function encrypt($data)
    {
        if (empty($data)) {
            throw new Exception\InvalidArgumentException('The data to encrypt cannot be empty');
        }
        if (null === $this->getKey()) {
            throw new Exception\InvalidArgumentException('No key specified for the encryption');
        }
        if (null === $this->getSalt()) {
            throw new Exception\InvalidArgumentException('The salt (IV) cannot be empty');
        }
        if (null === $this->getPadding()) {
            throw new Exception\InvalidArgumentException('You have to specify a padding method');
        }

        //в чем соль
        $iv = $this->getSalt();

        // encryption
        $result = mcrypt_encrypt(
            $this->supportedAlgos[$this->algo], $this->getKey(), $data, $this->supportedModes[$this->mode], $iv
        );

        return $iv . $result;
    }

}
