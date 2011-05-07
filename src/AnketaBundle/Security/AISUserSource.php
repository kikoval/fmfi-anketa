<?php
/**
 * This file contains user source interface
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

use Doctrine\ORM\EntityManager;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\Subject;
use AnketaBundle\Integration\AISRetriever;
use AnketaBundle\Entity\Role;

class AISUserSource implements UserSourceInterface
{

    /**
     * Doctrine repository for Subject entity
     * @var AnketaBundle\Entity\Repository\SubjectRepository
     */
    private $subjectRepository;

    /**
     * Doctrine repository for Role entity
     * @var AnketaBundle\Entity\Repository\RoleRepository
     */
    private $roleRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var AISRetriever */
    private $aisRetriever;

    /** @var array(array(rok,semester)) */
    private $semestre;

    /** @var boolean */
    private $loadAuth;

    public function __construct(EntityManager $em, AISRetriever $aisRetriever,
                                array $semestre, $loadAuth)
    {
        $this->entityManager = $em;
        $this->subjectRepository = $em->getRepository('AnketaBundle:Subject');
        $this->roleRepository = $em->getRepository('AnketaBundle:Role');
        $this->aisRetriever = $aisRetriever;
        $this->semestre = $semestre;
        $this->loadAuth = $loadAuth;
    }

    public function load(UserBuilder $builder)
    {
        $this->loadSubjects($builder);

        if (!$builder->hasFullName()) {
            $builder->setFullName($this->aisRetriever->getFullName());
        }

        if ($this->loadAuth) {
            // Not sure whether this works correctly in all cases
            if ($this->aisRetriever->isAdministraciaStudiaAllowed()) {
                $builder->addRole($this->findOrCreateRole('ROLE_AIS_STUDENT'));
                $builder->markStudent();
            }
        }

        $this->aisRetriever->logoutIfNotAlready();
    }

    /**
     * Load subject entities associated with this user
     */
    private function loadSubjects(UserBuilder $builder)
    {
        $aisPredmety = $this->aisRetriever->getPredmety();

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

            $builder->addSubject($subject);
        }
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

    // TODO(anty): move to role repository
    private function findOrCreateRole($name) {
        $role = $this->roleRepository->findOneBy(array('name' => $name));
        if ($role == null) {
            $role = new Role($name);
            $this->entityManager->persist($role);
        }
        return $role;
    }

}