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
use Doctrine\Common\Collections\ArrayCollection;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\Department;
use AnketaBundle\Entity\Season;

/**
 * @ORM\Entity()
 */
class UserSeason {
    
    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userSeasons")
     *
     * @var User $user
     */
    protected $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Season")
     *
     * @var Season $season
     */
    protected $season;
    
    /**
     * Do ktorej katedry pouzivatel patri (ktora ho zamestnava, etc.).
     * Studenti (asi okrem doktorandov) nepatria do ziadnej katedry a preto tu maju null.
     * 
     * @ORM\ManyToOne(targetEntity="Department")
     *
     * @var Department $department
     */
    protected $department;
    
    /**
     * Ci je pouzivatel v tejto sezone student.
     * Od tohto flagu sa odvija napriklad moznost hlasovat v ankete.
     * 
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    protected $isStudent;
    
    /**
     * Ci pouzivatel vyplnil odpoved na aspon jednu otazku
     * 
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    protected $participated;
    
    /**
     * Ci pouzivatel dohlasoval (anonymizoval) v tejto sezone.
     * Akonahle je nastavene na true, tak uz nemoze hlasovat.
     * Pri anonymizacii sa nastavi na true.
     * 
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    protected $finished;

    /**
     * Ci pouzivatel je v tejto sezone ucitelom.
     *
     * @ORM\Column(type="boolean")
     *
     * @var boolean
     */
    protected $isTeacher;
    
    public function __construct() {
        $this->department = null;
        $this->isStudent = false;
        $this->finished = false;
        $this->isTeacher = false;
        $this->participated = false;
        $this->season = null;
        $this->user = null;
    }
    
    /**
     * @return AnketaBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getDepartment() {
        return $this->department;
    }

    public function setDepartment($department) {
        $this->department = $department;
    }

    public function getSeason() {
        return $this->season;
    }

    public function setSeason($season) {
        $this->season = $season;
    }

    public function getId() {
        return $this->id;
    }
    
    public function getIsStudent() {
        return $this->isStudent;
    }

    public function setIsStudent($isStudent) {
        $this->isStudent = $isStudent;
    }

    public function getParticipated() {
        return $this->participated;
    }

    public function setParticipated($participated) {
        $this->participated = $participated;
    }

    public function getFinished() {
        return $this->finished;
    }

    public function setFinished($finished) {
        $this->finished = $finished;
    }

    public function canVote() {
        return ($this->getIsStudent() && !$this->getFinished());
    }

    public function getIsTeacher() {
        return $this->isTeacher;
    }

    public function setIsTeacher($isTeacher) {
        $this->isTeacher = $isTeacher;
    }

    
}
