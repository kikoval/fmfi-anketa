<?php
/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Entity
 * @author     Martin Sucha
 */

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AnketaBundle\Entity\SubjectSeason;
use AnketaBundle\Entity\Department;

/**
 * @ORM\Entity()
 * @ORM\Table(name="SubjectSeasonDepartment",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="subject_season_department_unique",columns={"subjectSeason_id","department_id"})})
 */
class SubjectSeasonDepartment {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SubjectSeason")
     *
     * @var SubjectSeason $subjectSeason
     */
    private $subjectSeason;

    /**
     * @ORM\ManyToOne(targetEntity="Department")
     *
     * @var Department $department
     */
    private $department;

    public function getSubjectSeason() {
        return $this->subject;
    }

    public function setSubjectSeason($subject) {
        $this->subject = $subject;
    }

    public function getDepartment() {
        return $this->department;
    }

    public function setDepartment($department) {
        $this->department = $department;
    }

    public function getId() {
        return $this->id;
    }
}