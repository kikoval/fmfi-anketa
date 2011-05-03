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
use AnketaBundle\Entity\Subject;
use AnketaBundle\Integration\AISRetriever;

class AnketaUserProvider implements UserProviderInterface
{
    
    /**
     * Doctrine repository for User entity
     * @var AnketaBundle\Entity\Repository\UserRepository
     */
    private $userRepository;

    /**
     * Doctrine repository for Subject entity
     * @var AnketaBundle\Entity\Repository\SubjectRepository
     */
    private $subjectRepository;
    
    /** @var EntityManager */
    private $entityManager;

    /** @var AISRetriever */
    private $aisRetriever;

    /** @var array(array(rok,semester)) */
    private $semestre;

    /** @var boolean */
    private $useAIS;

    public function __construct(EntityManager $em, AISRetriever $aisRetriever,
                                array $semestre, $useAIS)
    {
        $this->entityManager = $em;
        $this->userRepository = $em->getRepository('AnketaBundle:User');
        $this->subjectRepository = $em->getRepository('AnketaBundle:Subject');
        $this->aisRetriever = $aisRetriever;
        $this->semestre = $semestre;
        $this->useAIS = $useAIS;
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
            $fullname = null;

            // TODO(anty): load data from LDAP here

            if ($this->useAIS) {
                $subjects = $this->getSubjects();
                if ($fullname == null) {
                    $fullname = $this->aisRetriever->getFullName();
                }
                $this->aisRetriever->logoutIfNotAlready();
            }
            else {
                $subjects = array();
            }

            if ($fullname == null) {
                $fullname = $username;
            }

            $user = new User($username, $fullname);
            foreach ($subjects as $subject) {
                $user->addSubject($subject);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

        } 
        
        if ($user === null) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }
        
        return $user;
    }

    /**
     * Get subject entities associated with this user
     */
    private function getSubjects()
    {
        $aisPredmety = $this->aisRetriever->getPredmety();
        $subjects = array();

        foreach ($aisPredmety as $aisPredmet) {
            if (!$this->jeAktualny($aisPredmet['akRok'], $aisPredmet['semester'])) {
                continue;
            }

            $dlhyKod = $aisPredmet['skratka'];
            $kratkyKod = $this->getKratkyKod($dlhyKod);

            $subject = $this->subjectRepository->findOneBy(array('code' => $kratkyKod));
            if ($subject == null) {
                $subject = new Subject($aisPredmet['nazov']);
                $subject->setCode($kratkyKod);
                $this->entityManager->persist($subject);
            }

            $subjects[] = $subject;
        }

        return $subjects;
    }

    private function getKratkyKod($dlhyKod)
    {
        $matches = array();
        if (preg_match('@^[^/]*/([^/]+)/@', $dlhyKod, $matches) !== 1) {
            // TODO(anty): ignorovat vynimku?
            throw new \Exception('Nepodarilo sa zistit kratky kod predmetu');
        }

        $kratkyKod = $matches[1];
        return $kratkyKod;
    }

    private function jeAktualny($akRok, $semester)
    {
        return in_array(array($akRok, $semester), $this->semestre);
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