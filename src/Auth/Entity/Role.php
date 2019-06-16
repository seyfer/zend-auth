<?php

namespace Auth\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Role
 *
 * @author seyfer
 * @ORM\Entity
 * @ORM\Table(name="roles")
 */
class Role extends \ZendBaseModel\Entity\BaseEntity
{

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @ORM\OneToMany(targetEntity="\Auth\Entity\User", mappedBy="role")
     * @var ArrayCollection
     */
    protected $users;

    /**
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $name;

    public function getUsers()
    {
        return $this->users;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setUsers(ArrayCollection $users)
    {
        $this->users = $users;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

}
