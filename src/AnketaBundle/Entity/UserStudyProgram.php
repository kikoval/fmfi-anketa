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
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\StudyProgram;
use AnketaBundle\Entity\Season;

/**
 * Pre reporty, mozno vyhodime
 * @ORM\Entity()
 */
class UserStudyProgram {
    
    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
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
     * @ORM\ManyToOne(targetEntity="StudyProgram")
     * 
     * @var StudyProgram $studyProgram
     */
    protected $studyProgram;

    public function getSeason() {
        return $this->season;
    }

    public function setSeason($season) {
        $this->season = $season;
    }

    public function getId() {
        return $this->id;
    }
    
    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getStudyProgram() {
        return $this->studyProgram;
    }

    public function setStudyProgram($studyProgram) {
        $this->studyProgram = $studyProgram;
    }
    
}
