<?php

namespace Auth\Adapter;

use Auth\Entity\Role;
use Auth\Entity\SampleUser;
use Auth\Model\Authorization;
use Auth\Model\SampleMcrypt;
use Sender\Sender;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Session\Container;

/**
 * Description of SampleAdapter
 *
 * @author seyfer
 */
class SampleAdapter implements AdapterInterface
{

    const STATUS_OK = 2;
    const STATUS_ERROR = 3;
    const STATUS_WARNING = 1;

    /**
     * FOR TEST
     *
     * @param $username
     * @param $password
     */
    public $username;
    public $password;
    public $passwordCrypted;
    public $authStatus;
    private $site;
    private $contractId;
    private $secretKey = "";
    private $url = "";
    private $sampleAuthContainer;
    private $sampleAuthContainerName = "sampleAuth";

    public function __construct($username = null, $password = null)
    {
        if ($username && $password) {
            $this->setIdentity($username)->setCredential($password);
        }
    }

    public function getSite()
    {
        return $this->site;
    }

    public function getSecretKey()
    {
        return $this->secretKey;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getStatus()
    {
        return $this->authStatus;
    }

    protected function setStatus($status)
    {
        $this->authStatus = $status;

        return $this;
    }

    protected function setStatusWarning()
    {
        $this->setStatus(self::STATUS_WARNING);

        return $this;
    }

    protected function setStatusError()
    {
        $this->setStatus(self::STATUS_ERROR);

        return $this;
    }

    public function setContractId($contractId)
    {
        $this->contractId = $contractId;

        return $this;
    }

    public function getContractId()
    {
        return $this->contractId;
    }

    /**
     * контракты из сессии
     *
     * @return
     */
    public function getAvailableContracts()
    {
        return $this->getAuthContainer()->contracts;
    }

    public function setIdentity($username)
    {
        $this->username = $username;

        return $this;
    }

    public function setCredential($password)
    {
        if ($password) {
            $this->password = $password;
            $this->generatePasswordCrypted();
        }

        return $this;
    }

    /**
     * установить зашифрованный пароль
     */
    protected function generatePasswordCrypted()
    {
        $mcrypt = new SampleMcrypt();

        $mcrypt->setCalculatedSalt();
        $mcrypt->setKey($this->secretKey);

        $this->passwordCrypted = base64_encode($mcrypt->encrypt($this->password));
    }

    /**
     * попытка авторизации
     *
     * @throws \Auth\Model\Exception
     * @throws \Exception
     */
    protected function actionLoginAccount()
    {
        $authRequestParams = [
            'type' => 'LoginAccount',
            'data' => [
                'email' => $this->username,
                'password' => $this->passwordCrypted,
                'site' => $this->site,
                'contract_id' => $this->getContractId(),
            ]];

        $result = (new Sender())
            ->sendPost($this->url, $authRequestParams);

        $resultUnser = unserialize($result);

        //ошибка это конец
        if (isset($resultUnser['error']) && $resultUnser['error']) {

            $error = iconv("cp1251", "utf8", $resultUnser['error']);

            $this->setStatusError();
            throw new \Exception($error);
        }

        //выбрать контракт
        if (isset($resultUnser['warning']) && $resultUnser['warning']) {

            $warning = iconv("cp1251", "utf8", $resultUnser['warning']);
            $this->setStatusWarning();

            $this->setAvailableContracts($resultUnser['contracts']);

            throw new \Exception($warning);
        }

        //save entity
        $this->fillSessionUser($resultUnser);
        //save raw
        $this->getAuthContainer()->rawUser = $resultUnser;

        return TRUE;
    }

    /**
     * fill user entity and save to session
     *
     * @param array $result
     */
    protected function fillSessionUser($result)
    {
        $resultFormatted = $result;
        $resultFormatted['isActive'] = $result['state'];
        $resultFormatted['name'] = iconv("cp1251", "utf8", $result['name']);
        $resultFormatted['surname'] = iconv("cp1251", "utf8", $result['surname']);

        $user = new SampleUser();
        $user->exchangeArray($resultFormatted);

        $role = new Role();
        $role->setName($result['contract']['subject']);

        $user->setRole($role);

        $auth = new Authorization();
        $auth->setSessionUser($user);
    }

    /**
     * записать в сессию
     *
     * @param $contracts
     */
    private function setAvailableContracts($contracts)
    {
        foreach ($contracts as $id => $contract) {
            $contractEnc[$id] = iconv("cp1251", "utf8", $contract);
        }

        $this->getAuthContainer()->contracts = $contractEnc;
    }

    /**
     * очистить контракты
     */
    public function clearAvailableContracts()
    {
        $this->getAuthContainer()->contracts = NULL;
    }

    /**
     * контейнер для авторизации
     *
     * @return Container
     */
    private function getAuthContainer()
    {
        if (!$this->sampleAuthContainer) {
            $this->sampleAuthContainer = new Container($this->sampleAuthContainerName);
        }

        return $this->sampleAuthContainer;
    }

    public function actionLogoutAccount()
    {
        $this->sampleAuthContainer = NULL;
    }

    /**
     *
     * @return \Zend\Authentication\Result
     * @throws Exception
     */
    public function authenticate()
    {
        try {

            $result = $this->actionLoginAccount();

            if ($result) {
                $identity = "user";
                $code = Result::SUCCESS;

                return new Result($code, $identity, ["Success"]);
            }

            throw new \Exception("Authentication Failed");
        } catch (\Exception $e) {
            $code = Result::FAILURE;
            $identity = "guest";

            return new Result($code, $identity, [$e->getMessage()]);
        }
    }

}
