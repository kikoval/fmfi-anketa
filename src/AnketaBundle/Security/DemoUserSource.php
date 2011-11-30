<?php
/**
 * This file contains user source that assigns all subjects to the user,
 * and grants him a voting right. Useful for demo version, where we can't use
 * AIS to provide such information.
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

class DemoUserSource implements UserSourceInterface
{

    /**
     * Doctrine repository for Subject entity
     * @var AnketaBundle\Entity\SubjectRepository
     */
    private $subjectRepository;

    /**
     * Doctrine repository for Role entity
     * @var AnketaBundle\Entity\RoleRepository
     */
    private $roleRepository;
    
    /**
     * Doctrine repository for StudyProgram entity
     * @var AnketaBundle\Entity\StudyProgramRepository
     */
    private $studyProgramRepository;

    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
        $this->subjectRepository = $em->getRepository('AnketaBundle:Subject');
        $this->studyProgramRepository = $em->getRepository('AnketaBundle:StudyProgram');
        $this->roleRepository = $em->getRepository('AnketaBundle:Role');
    }

    public function load(UserBuilder $builder)
    {
        $subjects = $this->subjectRepository->findAll();
        $studyPrograms = $this->studyProgramRepository->findAll();
        if (count($studyPrograms) == 0) {
            throw new \Exception('Chyba studijny program');
        }
        foreach ($subjects as $subject) {
            $builder->addSubject($subject, $studyPrograms[0]);
        }
        $builder->addRole($this->roleRepository->findOrCreateRole('ROLE_DEMO_USER'));
        $builder->markStudent();
    }
}