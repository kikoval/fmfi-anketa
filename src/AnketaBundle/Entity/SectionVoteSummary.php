<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="SectionVoteSummary")
 */
class SectionVoteSummary {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Season")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Season $season
     */
    protected $season;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User $teacher
     */
    protected $teacher;

    /**
     * @ORM\ManyToOne(targetEntity="Subject")
     *
     * @var Subject $subject
     */
    protected $subject;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     *
     * @var Category $category
     */
    protected $category;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $count;

    public function getId() {
        return $this->id;
    }

    public function getSeason() {
        return $this->season;
    }

    public function setSeason($season) {
        $this->season = $season;
    }

    public function getTeacher() {
        return $this->teacher;
    }

    public function setTeacher($value) {
        $this->teacher = $value;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setSubject($value) {
        $this->subject = $value;
    }

    public function getCategory() {
        return $this->category;
    }

    public function setCategory($value) {
        $this->category = $value;
    }

    public function getCount() {
        return $this->count;
    }

    public function setCount($value) {
        $this->count = $value;
    }

}
