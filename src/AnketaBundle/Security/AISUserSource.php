<?php
/**
 * This file contains user source interface
 *
 * @copyright Copyright (c) 2011-2012 The FMFI Anketa authors (see AUTHORS).
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
use AnketaBundle\Lib\SubjectIdentificationInterface;
use AnketaBundle\Lib\SubjectIdentification;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class AISUserSource implements UserSourceInterface
{

    /**
     * Doctrine repository for Subject entity
     * @var AnketaBundle\Entity\SubjectRepository
     */
    private $subjectRepository;

    /**
     * Doctrine repository for StudyProgram entity
     * @var AnketaBundle\Entity\StudyProgramRepository
     */
    private $studyProgramRepository;

    /**
     * Doctrine repository for Role entity
     * @var AnketaBundle\Entity\RoleRepository
     */
    private $roleRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var Connection */
    private $dbConn;

    /** @var AISRetriever */
    private $aisRetriever;

    /** @var array(array(rok,semester)) */
    private $semestre;

    /** @var boolean */
    private $loadAuth;

    /** @var LoggerInterface */
    private $logger;

    /** @var SubjectIdentificationInterface */
    private $subjectIdentification;

    public function __construct(Connection $dbConn, EntityManager $em, AISRetriever $aisRetriever,
            array $semestre, $loadAuth, SubjectIdentificationInterface $subjectIdentification,
            LoggerInterface $logger = null)
    {
        $this->dbConn = $dbConn;
        $this->entityManager = $em;
        $this->subjectRepository = $em->getRepository('AnketaBundle:Subject');
        $this->roleRepository = $em->getRepository('AnketaBundle:Role');
        $this->studyProgramRepository = $em->getRepository('AnketaBundle:StudyProgram');
        $this->aisRetriever = $aisRetriever;
        $this->semestre = $semestre;
        $this->loadAuth = $loadAuth;
        $this->logger = $logger;
        $this->subjectIdentification = $subjectIdentification;
    }

    public function load(UserSeason $userSeason)
    {
        $user = $userSeason->getUser();
        if ($user->getDisplayName() === null) {
            $user->setDisplayName($this->aisRetriever->getFullName());
        }

        if ($this->aisRetriever->isAdministraciaStudiaAllowed()) {
            $this->loadSubjects($userSeason);

            if ($this->loadAuth) {
                $userSeason->setIsStudent(true);
            }
        }

        $this->aisRetriever->logoutIfNotAlready();
        return true;
    }

    /**
     * Load subject entities associated with this user
     */
    private function loadSubjects(UserSeason $userSeason)
    {
        $aisPredmety = $this->aisRetriever->getPredmety($this->semestre);

        $slugy = array();

        foreach ($aisPredmety as $aisPredmet) {
            $props = $this->subjectIdentification->identify($aisPredmet['skratka'], $aisPredmet['nazov']);

            // Ignorujme duplicitne predmety
            if (in_array($props['slug'], $slugy)) {
                continue;
            }
            $slugy[] = $props['slug'];

            // vytvorime subject v DB ak neexistuje
            // pouzijeme INSERT ON DUPLICATE KEY UPDATE
            // aby sme nedostavali vynimky pri raceoch
            $stmt = $this->dbConn->prepare("INSERT INTO Subject (code, name, slug) VALUES (:code, :name, :slug) ON DUPLICATE KEY UPDATE slug=slug");
            $stmt->bindValue('code', $props['code']);
            $stmt->bindValue('name', $props['name']);
            $stmt->bindValue('slug', $props['slug']);
            $stmt->execute();

            $subject = $this->subjectRepository->findOneBy(array('slug' => $props['slug']));
            if ($subject == null) {
                throw new \Exception("Nepodarilo sa pridať predmet do DB");
            }
            $stmt = null;

            // Vytvorime studijny program v DB ak neexistuje
            // podobne ako predmet vyssie
            $stmt = $this->dbConn->prepare("INSERT INTO StudyProgram (code, name, slug) VALUES (:code, :name, :slug) ON DUPLICATE KEY UPDATE code=code");
            $stmt->bindValue('code', $aisPredmet['studijnyProgram']['skratka']);
            $stmt->bindValue('name', $aisPredmet['studijnyProgram']['nazov']);
            // TODO(anty): toto nezarucuje, ze to je vhodny string
            // treba pouzivat whitelist namiesto blacklistu!
            $stmt->bindValue('slug', $this->generateSlug($aisPredmet['studijnyProgram']['skratka']));
            $stmt->execute();

            $studyProgram = $this->studyProgramRepository->findOneBy(array('code' => $aisPredmet['studijnyProgram']['skratka']));
            if ($studyProgram == null) {
                throw new \Exception("Nepodarilo sa pridať študijný program do DB");
            }
            $stmt = null;

            $userSubject = new UsersSubjects();
            $userSubject->setUser($userSeason->getUser());
            $userSubject->setSeason($userSeason->getSeason());
            $userSubject->setSubject($subject);
            $userSubject->setStudyProgram($studyProgram);

            $this->entityManager->persist($userSubject);
        }
    }

    /**
     * @todo presunut do samostatnej triedy a spravit lepsie
     */
    private function generateSlug($slug)
    {
        $slug = preg_replace('@[^a-zA-Z0-9_]@', '-', $slug);
        $slug = preg_replace('@-+@', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

}
