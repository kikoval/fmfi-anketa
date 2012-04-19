<?php
/**
 * This file contains user provider for Anketa
 *
 * @copyright Copyright (c) 2011,2012 The FMFI Anketa authors (see AUTHORS).
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
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\Season;
use AnketaBundle\Entity\UserSeason;
use AnketaBundle\Entity\UsersSubjects;

class AnketaUserProvider implements UserProviderInterface
{

    /**
     * Doctrine repository for User entity
     * @var AnketaBundle\Entity\UserRepository
     */
    private $userRepository;
    
    /**
     * Doctrine repository for UserSeason entity
     * @var AnketaBundle\Entity\UserSeasonRepository
     */
    private $userSeasonRepository;
    
    /**
     * Doctrine repository for Role entity
     * @var AnketaBundle\Entity\RoleRepository
     */
    private $roleRepository;
    
    /**
     * Doctrine repository for Season entity
     * @var AnketaBundle\Entity\SeasonRepository
     */
    private $seasonRepository;

    /**
     * Doctrine repository for Teacher entity
     * @var AnketaBundle\Entity\SeasonRepository
     */
    private $teacherRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var UserSourceInterface[] */
    private $perSeasonUserSources;
    
    /** @var UserSourceInterface[] */
    private $perLoginUserSources;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(EntityManager $em, array $perSeasonUserSources, array $perLoginUserSources, LoggerInterface $logger = null)
    {
        $this->entityManager = $em;
        $this->userRepository = $em->getRepository('AnketaBundle:User');
        $this->userSeasonRepository = $em->getRepository('AnketaBundle:UserSeason');
        $this->roleRepository = $em->getRepository('AnketaBundle:Role');
        $this->seasonRepository = $em->getRepository('AnketaBundle:Season');
        $this->teacherRepository = $em->getRepository('AnketaBundle:Teacher');
        $this->perSeasonUserSources = $perSeasonUserSources;
        $this->perLoginUserSources = $perLoginUserSources;
        $this->logger = $logger;
    }

    /**
     * Reload a user given an existing UserInterface instance
     * @param UserInterface $user to reload
     * @return User the reloaded user
     * @throws UnsupportedUserException if the UserInstance given is not User
     */
    public function refreshUser(UserInterface $oldUser) {
        if ($this->logger) {
            $this->logger->debug(sprintf('refreshing user %s object %s', $oldUser->getUsername(), get_class($oldUser)));
        }
        if (!($oldUser instanceof User)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($oldUser)));
        }
        
        if ($this->logger) {
            $this->logger->debug('Searching in database');
        }
        $user = $this->userRepository->findOneWithRolesByUserName($oldUser->getUserName());

        if ($user === null) {
            if ($this->logger) {
                $this->logger->debug('not found in database');
            }
            throw new UsernameNotFoundException(sprintf("User %s not found in database!", $oldUser->getUserName()));
        }
        
        $user->setOrgUnits($oldUser->getOrgUnits());
        $this->loadUserInfo($user, false);
        
        if ($this->logger) {
              $this->logger->debug('Returning the user');
        }

        return $user;
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
            $user = new User($username);
            $this->entityManager->persist($user);
            
            $user->addRole($this->roleRepository->findOrCreateRole('ROLE_USER'));
        }
        
        assert($user !== null);
        $this->loadUserInfo($user, true);
        return $user;
    }
    
    private function loadUserInfo(User $user, $firstTime) {
        $activeSeason = $this->seasonRepository->getActiveSeason();
        $userSeason = $this->userSeasonRepository->
                findOneBy(array('user' => $user->getId(), 'season' => $activeSeason->getId()));
        $foundUser = !$firstTime;

        if ($userSeason === null) {
            $userSeason = new UserSeason();
            $userSeason->setUser($user);
            $userSeason->setSeason($activeSeason);
            $this->entityManager->persist($userSeason);
            
            foreach ($this->perSeasonUserSources as $userSource) {
                $found = $userSource->load($userSeason);

                if ($this->logger) {
                    $this->logger->debug(sprintf('Per season user source %s returned %s',
                        get_class($userSource), $found));
                }

                $foundUser |= $found;
            }
        }
        
        if ($firstTime) {
            foreach ($this->perLoginUserSources as $userSource) {
                $found = $userSource->load($userSeason);

                if ($this->logger) {
                    $this->logger->debug(sprintf('Per login user source %s returned %s',
                        get_class($userSource), $found));
                }

                $foundUser |= $found;
            }
        }
        
        if (!$foundUser) {
            if ($this->logger) {
                $this->logger->debug('User info not found in loadUserInfo');
            }
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $user->getUserName()));
        }
        
        $this->entityManager->flush();
        
        // TODO: toto vyhodit s presunom teacherov do usera
        $teacher = $this->teacherRepository->findOneBy(array('login' => $user->getUserName()));
        if ($teacher !== null) {
            if (!$user->hasRole('ROLE_TEACHER')) {
                $user->addNonPersistentRole('ROLE_TEACHER');
            }
        }
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