<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use \DateTime;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\SeasonRepository")
 */
class Season {

    /**
     * @ORM\Id @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Full name, i.e. 2010/2011
     * @ORM\Column(type="string")
     */
    private $description;
    
    /**
     * Total number of students in this season
     * @ORM\Column(type="integer")
     * @var int $studentCount
     */
    private $studentCount;

    /**
     * Slug - unique descriptive ID to be used in URLs.
     *
     * For example 2010-2011
     *
     * @ORM\Column(type="string", unique="true")
     * @var string $slug
     */
    private $slug;

    public function __construct($description, $slug) {
        $this->setDescription($description);
        $this->setSlug($slug);
    }

    public function getId() {
        return $this->id;
    }

    public function setDescription($value) {
        $this->description = $value;
    }

    public function getDescription() {
        return $this->description;
    }
    
    public function getStudentCount() {
        return $this->studentCount;
    }

    public function setStudentCount($studentCount) {
        $this->studentCount = $studentCount;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function setSlug($value) {
        $this->slug = $value;
    }

}