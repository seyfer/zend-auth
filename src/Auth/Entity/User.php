<?php

namespace Auth\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of User
 *
 * @author seyfer
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends \ZendBaseModel\Entity\BaseEntity
{

    public function __construct()
    {
        $this->role = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @ORM\ManyToOne(targetEntity="\Auth\Entity\Role", inversedBy="users", fetch="EAGER")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     * @var Role
     */
    protected $role;

    /**
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $email;

    /**
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $login;

    /**
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $password;

    /**
     *
     * @var int
     * @ORM\Column(type="integer", name="is_active", length=1, nullable=false)
     */
    protected $isActive = 0;

    /**
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $surname;

    /**
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fathername;

    public function getRole()
    {
        return $this->role;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function getFathername()
    {
        return $this->fathername;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    public function setFathername($fathername)
    {
        $this->fathername = $fathername;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function setPassword($password)
    {
        $passwdMD5 = md5($password);

        $this->password = $passwdMD5;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

}
