<?php

namespace Auth\Entity;

/**
 * Description of SampleUser
 *
 * @author seyfer
 */
class SampleUser extends User
{

    /**
     *
     * @var int
     */
    protected $site;

    /**
     *
     * @var string
     */
    protected $phone;

    /**
     *
     * @var int
     */
    protected $contractId;

    /**
     *
     * @var array
     */
    protected $contracts = [];

    /**
     *
     * @var array
     */
    protected $contract = [];

    /**
     *
     * @var array
     */
    protected $contractor = [];

    public function getSite()
    {
        return $this->site;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getContractId()
    {
        return $this->contractId;
    }

    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    public function setContractId($contractId)
    {
        $this->contractId = $contractId;

        return $this;
    }

    public function getContracts()
    {
        return $this->contracts;
    }

    public function getContract()
    {
        return $this->contract;
    }

    public function getContractor()
    {
        return $this->contractor;
    }

    public function setContracts($contracts)
    {
        $this->contracts = $contracts;

        return $this;
    }

    public function setContract($contract)
    {
        $this->contract = $contract;

        return $this;
    }

    public function setContractor($contractor)
    {
        $this->contractor = $contractor;

        return $this;
    }

}
