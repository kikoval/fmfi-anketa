<?php
/**
 * This file contains user source that assigns all subjects to the user,
 * and grants him a voting right. Useful for demo version, where we can't use
 * AIS to provide such information.
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

use Doctrine\ORM\EntityManager;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\UserSeason;
use AnketaBundle\Entity\UsersSubjects;
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
    
    /** @var array */
    private $orgUnits;

    public function __construct(EntityManager $em, array $orgUnits = null)
    {
        $this->entityManager = $em;
        $this->subjectRepository = $em->getRepository('AnketaBundle:Subject');
        $this->studyProgramRepository = $em->getRepository('AnketaBundle:StudyProgram');
        $this->roleRepository = $em->getRepository('AnketaBundle:Role');
        $this->orgUnits = $orgUnits;
    }

    public function load(UserSeason $userSeason)
    {
        $user = $userSeason->getUser();
        // prvych 10 predmetov a prvy najdeny studijny program
        $subjects = $this->subjectRepository->findBy(array(), null, 20);
        $studyPrograms = $this->studyProgramRepository->findBy(array(), null, 2);
        if (count($studyPrograms) == 0) {
            throw new \Exception('Chyba studijny program');
        }
        
        foreach ($subjects as $index => $subject) {
            $userSubject = new UsersSubjects();
            $userSubject->setUser($user);
            $userSubject->setSeason($userSeason->getSeason());
            $userSubject->setSubject($subject);
            $studyProgram = $studyPrograms[$index % count($studyPrograms)];
            $userSubject->setStudyProgram($studyProgram);
            
            $this->entityManager->persist($userSubject);
        }
        if ($this->orgUnits !== null) {
            $user->setOrgUnits($this->orgUnits);
        }
        $userSeason->setIsStudent(true);
        
        return true;
    }
}
