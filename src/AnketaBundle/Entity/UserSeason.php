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
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User $user
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Season")
     *
     * @var Season $season
     */
    private $season;
    
    /**
     * Do ktorej katedry pouzivatel patri (ktora ho zamestnava, etc.).
     * Studenti (asi okrem doktorandov) nepatria do ziadnej katedry a preto tu maju null.
     * @ORM\ManyToOne(targetEntity="Department")
     *
     * @var Department $department
     */
    private $department;
    
    /**
     * Ci je/bol pouzivatel opravneny hlasovat v tejto sezone.
     * Null, ak sa este nerozhodlo (napr. sa v tejto sezone neprihlasil).
     * Pri anonymizacii sa tento flag necha na povodnej hodnote.
     * @ORM\Column(type="boolean", nullable="true")
     * @var boolean
     */
    private $eligible;
    
    /**
     * Ci pouzivatel vyplnil odpoved na aspon jednu otazku
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $participated;
    
    /**
     * Ci pouzivatel dohlasoval (anonymizoval) v tejto sezone.
     * Akonahle je nastavene na true, tak uz nemoze hlasovat.
     * Pri anonymizacii sa nastavi na true.
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $finished;
    
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
    
    public function getEligible() {
        return $this->eligible;
    }

    public function setEligible($eligible) {
        $this->eligible = $eligible;
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

    
}
