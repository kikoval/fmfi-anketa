<?php
/**
 * This file contains user builder
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Security
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Security;

use AnketaBundle\Entity\Role;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\Subject;

class UserBuilder
{

    /** @var string|null */
    private $username;

    /** @var string|null */
    private $fullName;

    /** @var Role[] */
    private $roles;

    /** @var Subject[] */
    private $subjects;

    /** @var boolean */
    private $isStudent;

    public function __construct()
    {
        $this->username = null;
        $this->fullName = null;
        $this->roles = array();
        $this->subjects = array();
        $this->isStudent = false;
    }

    /**
     * Set username.
     * 
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Set full name
     * @param string $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * Return true if the full name has been already set
     * @return boolean true if set
     */
    public function hasFullName()
    {
        return $this->fullName !== null;
    }

    /**
     * Add a role be added to the user.
     *
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        $this->roles[] = $role;
    }

    /**
     * Add a subject to be added to the user.
     *
     * @param Subject $subject
     */
    public function addSubject(Subject $subject)
    {
        $this->subjects[] = $subject;
    }

    /**
     * Mark the user as being a student.
     */
    public function markStudent()
    {
        $this->isStudent = true;
    }

    /**
     * Create the user according to this builder.
     */
    public function createUser()
    {
        if ($this->username == null) {
            throw new \LogicException('Username for new user must be set');
        }

        if ($this->fullName == null) {
            $this->fullName = $this->username;
        }

        $user = new User($this->username, $this->fullName);
        $user->setHasVote($this->isStudent);
        foreach ($this->roles as $role) {
            $user->addRole($role);
        }
        foreach ($this->subjects as $subject) {
            $user->addSubject($subject);
        }

        return $user;
    }

}