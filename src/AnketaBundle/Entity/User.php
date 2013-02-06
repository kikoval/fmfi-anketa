<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\UserRepository")
 */
class User implements UserInterface, EquatableInterface {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $displayName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $givenName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $familyName;

    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="UsersRoles",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     */
    protected $roles;

    /**
     * @ORM\OneToMany(targetEntity="UserSeason", mappedBy="user")
     */
    protected $userSeasons;

    /**
     * Roles that are not persisted in the database
     * @var array(string)
     */
    protected $nonPersistentRoles = array(); // inicializator musi byt tu! (doctrine nevola konstruktor)

    /**
     * List of user's organizational units
     * This is not persisted in the database, as it is always reloaded
     * @var array(string)
     */

    protected $orgUnits = array(); // inicializator musi byt tu! (doctrine nevola konstruktor)
    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $login;

    /**
     * @ORM\ManyToOne(targetEntity="Department")
     * @var Department
     */
    protected $department;

    /**
     * @param String $username
     */
    public function __construct($login) {
        $this->roles = new ArrayCollection();
        $this->userSeasons = new ArrayCollection();
        $this->setLogin($login);
    }

    public function getId() {
        return $this->id;
    }

    public function setDisplayName($value) {
        $this->displayName = $value;
    }

    public function getDisplayName() {
        return $this->displayName;
    }

    /**
     * @param ArrayCollection $value
     */
    public function setRoles($value) {
        $this->roles = $value;
    }

    /**
     * @param Role $value
     */
    public function addRole($value) {
        $this->roles[] = $value;
    }

    /**
     * Add a role that is not persisted in the database
     * @param string $value
     */
    public function addNonPersistentRole($value) {
        $this->nonPersistentRoles[] = $value;
    }

    /**
     * @return string[] roles
     */
    public function getRoles() {
        $roles = array();
        foreach ($this->roles as $role) {
            $roles[] = $role->getRole();
        }
        $roles = array_merge($roles, $this->nonPersistentRoles);
        return $roles;
    }

    public function hasRole($role) {
        if ($role instanceof Role) {
            $role = $role->getRole();
        }
        return array_search($role, $this->getRoles()) !== false;
    }

    public function getOrgUnits() {
        return $this->orgUnits;
    }

    public function setOrgUnits($orgUnits) {
        $this->orgUnits = $orgUnits;
    }

    public function isEqualTo(UserInterface $user) {
        if (!$user instanceof User) {
            return false;
        }

        return $this->login === $user->getLogin();
    }

    public function eraseCredentials() {
    }

    public function getPassword() {
        return null;
    }

    public function getSalt() {
        return null;
    }

    public function forSeason($season) {
        if ($season instanceof Season) $season = $season->getId();
        foreach ($this->getUserSeasons() as $us) {
            if ($us->getSeason()->getId() == $season) {
                return $us;
            }
        }
        return null;
    }


    public function __toString() {
        return $this->getLogin();
    }


    /**
     * Add userSeason
     *
     * @param UserSeason $userSeason
     */
    public function addUserSeason(\AnketaBundle\Entity\UserSeason $userSeason)
    {
        $this->userSeasons[] = $userSeason;
    }

    /**
     * Get userSeasons
     *
     * @return Collection
     */
    public function getUserSeasons()
    {
        return $this->userSeasons;
    }


    public function getName() {
        $name = trim($this->getGivenName() . ' ' . $this->getFamilyName());
        if ($name == '') return null;
        return $name;
    }

    public function getGivenName() {
        return $this->givenName;
    }

    public function setGivenName($givenName) {
        $this->givenName = $givenName;
    }

    public function getFamilyName() {
        return $this->familyName;
    }

    public function setFamilyName($familyName) {
        $this->familyName = $familyName;
    }


    public function getFormattedName() {
        $formattedName = $this->getDisplayName() ?: $this->getName() ?: $this->getLogin() ?: null;
        if ($formattedName === null) {
                throw new \Exception('Neda sa vygenerovat formatovane meno pre pouzivatela s id ' . $this->getId());
        }
        return $formattedName;
    }

    public function getLogin() {
        return $this->login;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    /**
     * @return Department
     */
    public function getDepartment() {
        return $this->department;
    }

    /**
     * @param Department $department
     */
    public function setDepartment($department) {
        $this->department = $department;
    }

    /**
     * Kvoli Symfony UserInterface
     */
    public function getUsername() {
        return $this->getLogin();
    }
}
