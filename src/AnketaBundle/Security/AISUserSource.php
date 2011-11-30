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
use Doctrine\DBAL\Connection;

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

    /** @var Connection */
    private $dbConn;

    /** @var AISRetriever */
    private $aisRetriever;

    /** @var array(array(rok,semester)) */
    private $semestre;

    /** @var boolean */
    private $loadAuth;

    public function __construct(Connection $dbConn, EntityManager $em, AISRetriever $aisRetriever,
                                array $semestre, $loadAuth)
    {
        $this->dbConn = $dbConn;
        $this->entityManager = $em;
        $this->subjectRepository = $em->getRepository('AnketaBundle:Subject');
        $this->roleRepository = $em->getRepository('AnketaBundle:Role');
        $this->aisRetriever = $aisRetriever;
        $this->semestre = $semestre;
        $this->loadAuth = $loadAuth;
    }

    public function load(UserBuilder $builder)
    {
        if (!$builder->hasFullName()) {
            $builder->setFullName($this->aisRetriever->getFullName());
        }
        
        if ($this->aisRetriever->isAdministraciaStudiaAllowed()) {
            $this->loadSubjects($builder);

            if ($this->loadAuth) {            
                $builder->addRole($this->roleRepository->findOrCreateRole('ROLE_AIS_STUDENT'));
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
        $aisPredmety = $this->aisRetriever->getPredmety($this->semestre);
        
        $kody = array();

        foreach ($aisPredmety as $aisPredmet) {
            $dlhyKod = $aisPredmet['skratka'];
            $kratkyKod = $this->getKratkyKod($dlhyKod);
            
            // Ignorujme duplicitne predmety
            if (in_array($kratkyKod, $kody)) {
                continue;
            }
            $kody[] = $kratkyKod;

            // vytvorime subject v DB ak neexistuje
            // pouzijeme INSERT ON DUPLICATE KEY UPDATE
            // aby sme nedostavali vynimky pri raceoch
            $stmt = $this->dbConn->prepare("INSERT INTO Subject (code, name) VALUES (:code, :name) ON DUPLICATE KEY UPDATE code=code");
            $stmt->bindValue('code', $kratkyKod);
            $stmt->bindValue('name', $aisPredmet['nazov']);
            $stmt->execute();

            $subject = $this->subjectRepository->findOneBy(array('code' => $kratkyKod));
            if ($subject == null) {
                throw new \Exception("Nepodarilo sa pridať predmet do DB");
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

}