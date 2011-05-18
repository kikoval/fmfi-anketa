<?php
/**
 * This file contains user provider for Anketa
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

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityManager;
use AnketaBundle\Entity\User;

class AnketaUserProvider implements UserProviderInterface
{

    /**
     * Doctrine repository for User entity
     * @var AnketaBundle\Entity\Repository\UserRepository
     */
    private $userRepository;

    /**
     * Doctrine repository for Role entity
     * @var AnketaBundle\Entity\Repository\RoleRepository
     */
    private $roleRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var UserSourceInterface[] */
    private $userSources;

    public function __construct(EntityManager $em, array $userSources)
    {
        $this->entityManager = $em;
        $this->userRepository = $em->getRepository('AnketaBundle:User');
        $this->roleRepository = $em->getRepository('AnketaBundle:Role');
        $this->userSources = $userSources;
    }

    /**
     * Reload a user given an existing UserInterface instance
     * @param UserInterface $user to reload
     * @return User the reloaded user
     * @throws UnsupportedUserException if the UserInstance given is not User
     */
    public function loadUser(UserInterface $user) {
        if (!($user instanceof User)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
        
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Load a user with given username.
     * 
     * This function tries to load the user from database first.
     * If that is not successful, try to construct a user from
     * LDAP and AIS2, if enabled.
     * 
     * @param string $username 
     * @return User the requested user
     * @throws UsernameNotFoundException if the given user cannot be found
     *                                   nor constructed
     */
    public function loadUserByUsername($username) {
        // Try to load the user from database first
        $user = $this->userRepository->findOneWithRolesByUserName($username);

        if ($user === null) {
            $builder = new UserBuilder();
            $builder->setUsername($username);
            $builder->addRole($this->roleRepository->findOrCreateRole('ROLE_USER'));

            foreach ($this->userSources as $userSource) {
                $userSource->load($builder);
            }

            $user = $builder->createUser();

            $this->entityManager->persist($user);
            $this->entityManager->flush();

        } 
        
        if ($user === null) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }
        
        return $user;
    }

    /**
     * Check whether this provider supports given user class
     * @param string $class classname
     * @return boolean true iff the class is AnketaBundle\Entity\User
     */
    public function supportsClass($class) {
        return $class === 'AnketaBundle\Entity\User';
    }

    
}