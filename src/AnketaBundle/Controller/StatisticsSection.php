<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Controller
 */

namespace AnketaBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use AnketaBundle\Entity\Season;
use AnketaBundle\Entity\Subject;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\StudyProgram;
use AnketaBundle\Entity\Answer;
use AnketaBundle\Entity\Response;
use AnketaBundle\Entity\CategoryType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class StatisticsSection extends ContainerAware {

    public static function getSectionOfAnswer(ContainerInterface $container, Answer $answer) {
        $category = $answer->getQuestion()->getCategory()->getType();
        if ($category == CategoryType::TEACHER_SUBJECT) return new StatisticsTeacherSubjectSection($container, $answer->getSeason(), $answer->getSubject(), $answer->getTeacher());
        if ($category == CategoryType::SUBJECT) return new StatisticsSubjectSection($container, $answer->getSeason(), $answer->getSubject());
        if ($category == CategoryType::GENERAL) return new StatisticsGeneralSection($container, $answer->getSeason(), $answer->getQuestion());
        if ($category == CategoryType::STUDY_PROGRAMME) return new StatisticsStudyProgramSection($container, $answer->getSeason(), $answer->getStudyProgram());
        throw new \Exception('Unknown category type');
    }

    public static function getSectionOfResponse(ContainerInterface $container, Response $response) {
        if ($response->getTeacher() !== null) return new StatisticsTeacherSubjectSection($container, $response->getSeason(), $response->getSubject(), $response->getTeacher());
        if ($response->getSubject() !== null) return new StatisticsSubjectSection($container, $response->getSeason(), $response->getSubject());
        if ($response->getQuestion() !== null) return new StatisticsGeneralSection($container, $response->getSeason(), $response->getQuestion());
        if ($response->getStudyProgram() !== null) return new StatisticsStudyProgramSection($container, $response->getSeason(), $response->getStudyProgram());
        throw new \Exception('Unknown type of response');
    }

    // TODO: mozno nie vracat null ale hadzat rozne exceptiony, nech sa da zistit co sa stalo
    public static function getSectionFromSlug(ContainerInterface $container, $slug) {
        $em = $container->get('doctrine.orm.entity_manager');
        if (!preg_match('@^([a-z0-9-]+)/(.*[^/])/*$@', $slug, $matches)) {
            throw new NotFoundHttpException('Section not found: Section slug doesn\'t start with season slug.');
        }
        $season = $em->getRepository('AnketaBundle:Season')->findOneBy(array('slug' => $matches[1]));
        if ($season === null) {
            throw new NotFoundHttpException('Section not found: Season "'.$matches[1].'" not found.');
        }
        $slug = $matches[2];
        if (preg_match('@^predmet/([a-zA-Z0-9-_]+)/ucitel/(\d+)$@', $slug, $matches)) {
            $subject = $em->getRepository('AnketaBundle:Subject')->findOneBy(array('slug' => $matches[1]));
            if ($subject === null) {
                throw new NotFoundHttpException('Section not found: Subject "'.$matches[1].'" not found.');
            }
            $teacher = $em->find('AnketaBundle:User', $matches[2]);
            if ($teacher === null) {
                throw new NotFoundHttpException('Section not found: Teacher "'.$matches[2].'" not found.');
            }
            return new StatisticsTeacherSubjectSection($container, $season, $subject, $teacher);
        }
        if (preg_match('@^predmet/([a-zA-Z0-9-_]+)$@', $slug, $matches)) {
            $subject = $em->getRepository('AnketaBundle:Subject')->findOneBy(array('slug' => $matches[1]));
            if ($subject === null) {
                throw new NotFoundHttpException('Section not found: Subject "'.$matches[1].'" not found.');
            }
            return new StatisticsSubjectSection($container, $season, $subject);
        }
        if (preg_match('@^vseobecne/(\d+)$@', $slug, $matches)) {
            $question = $em->find('AnketaBundle:Question', $matches[1]);
            if ($question === null) {
                throw new NotFoundHttpException('Section not found: Question "'.$matches[1].'" not found.');
            }
            return new StatisticsGeneralSection($container, $season, $question);
        }
        if (preg_match('@^program/([a-zA-Z0-9-_]+)$@', $slug, $matches)) {
            $program = $em->getRepository('AnketaBundle:StudyProgram')->findOneBy(array('slug' => $matches[1]));
            if ($program === null) {
                throw new NotFoundHttpException('Section not found: Program "'.$matches[1].'" not found.');
            }
            return new StatisticsProgramSection($container, $season, $program);
        }
        throw new NotFoundHttpException('Section not found: Bad section slug format.');
    }

    ///// II. the boring part: instance variables and their accessors

    protected $season = null;

    public function getSeason() {
        return $this->season;
    }
    
    protected $subject = null;

    public function getSubject() {
        return $this->subject;
    }

    protected $teacher = null;

    public function getTeacher() {
        return $this->teacher;
    }

    protected $generalQuestion = null;

    public function getGeneralQuestion() {
        return $this->generalQuestion;
    }

    protected $studyProgram = null;

    public function getStudyProgram() {
        return $this->studyProgram;
    }

    protected $title = null;

    public function getTitle() {
        return $this->title;
    }

    protected $headingVisible = true;

    public function getHeadingVisible() {
        return $this->headingVisible;
    }

    private $preface = null;

    public function getPreface() {
        if ($this->preface === null) {
            if ($this->getSubject() && $this->getSeason()) {
                $preface = '';
                $em = $this->container->get('doctrine.orm.entity_manager');
                $subject = $this->getSubject();
                $teacher = $this->getTeacher();
                $season = $this->getSeason();
                $skratka_fakulty = $this->container->getParameter('skratka_fakulty');
                $totalStudents = 0;
                $subjectSeason = $em->getRepository('AnketaBundle:SubjectSeason')->findOneBy(array(
                    'subject' => $subject,
                    'season' => $season
                ));
                if (isset($subjectSeason)) {
                    if ($subjectSeason->getStudentCountFaculty() !== null) {
                        $scf = $subjectSeason->getStudentCountFaculty();
                        $preface .= 'Tento predmet ';
                        if ($scf == 0) $preface .= 'nemal nikto z '.$skratka_fakulty.' zapísaný';
                        if ($scf == 1) $preface .= 'mal zapísaný '.$scf.' študent '.$skratka_fakulty;
                        if ($scf >= 2 && $scf <= 4) $preface .= 'mali zapísaní '.$scf.' študenti '.$skratka_fakulty;
                        if ($scf >= 5) $preface .= 'malo zapísaných '.$scf.' študentov '.$skratka_fakulty;
                        if ($subjectSeason->getStudentCountAll() !== null) {
                            $totalStudents = $subjectSeason->getStudentCountAll();
                            $sco = $totalStudents - $scf;
                            if ($sco) $preface .= ' ('.$sco.' z iných fakúlt)';
                        }
                        $preface .= '.';
                    }
                    else if ($subjectSeason->getStudentCountAll() !== null) {
                        $sca = $subjectSeason->getStudentCountAll();
                        $preface .= 'Tento predmet ';
                        if ($sca == 0) $preface .= 'nemal nikto zapísaný';
                        if ($sca == 1) $preface .= 'mal zapísaný '.$sca.' študent';
                        if ($sca >= 2 && $sca <= 4) $preface .= 'mali zapísaní '.$sca.' študenti';
                        if ($sca >= 5) $preface .= 'malo zapísaných '.$sca.' študentov';
                        $preface .= '.';
                        $totalStudents = $sca;
                    }

                }

                $studentov = function ($count) {
                    if ($count == 0) return 'sa nevyjadril žiaden študent';
                    if ($count == 1) return 'sa vyjadril jeden študent';
                    if ($count < 4) return 'sa vyjadrili '.$count.' študenti';
                    if ($count >= 4) return 'sa vyjadrilo '.$count.' študentov';
                };

                $votingSummary = $em->getRepository('AnketaBundle:SectionVoteSummary')->findOneBy(array(
                    'subject' => $subject,
                    'season' => $season,
                    'teacher' => $teacher
                ));
                if ($votingSummary) {
                    $voters = $votingSummary->getCount();
                    if ($teacher) {
                        $preface .= ' K tomuto vyučujúcemu ';
                    } else {
                        $preface .= ' K predmetu ';
                    }
                    $preface .= $studentov($voters);
                    if ($totalStudents) {
                        $preface .= ' ('.round($voters/$totalStudents * 100, 2). '% z '.$totalStudents.').';
                    } else {
                        $preface .= '.';
                    }
                }
                $this->preface = $preface;
            } else {
                $this->preface = '';
            }
        }
        return $this->preface;
    }

    protected $minVoters = 0;

    public function getMinVoters() {
        return $this->minVoters;
    }

    protected $questionsCategoryType = null;

    public function getQuestions() {
        if ($this->generalQuestion) return array($this->generalQuestion);
        $em = $this->container->get('doctrine.orm.entity_manager');
        return $em->getRepository('AnketaBundle:Question')->getOrderedQuestionsByCategoryType($this->questionsCategoryType, $this->season);
    }

    protected $answersQuery = null;

    public function getAnswers($question) {
        $query = array_merge($this->answersQuery, array('question' => $question->getId()));
        $em = $this->container->get('doctrine.orm.entity_manager');
        return $em->getRepository('AnketaBundle:Answer')->findBy($query);
    }

    // TODO public function getQuestionsAndAnswers() or something like that

    protected $responsesQuery = null;

    public function getResponses() {
        $em = $this->container->get('doctrine.orm.entity_manager');
        return $em->getRepository('AnketaBundle:Response')->findBy($this->responsesQuery);
    }

    protected $activeMenuItems = null;

    public function getActiveMenuItems() {
        return $this->activeMenuItems;
    }

    protected $slug = null;

    /**
     * Get slug for the section.
     * 
     * @return string
     */
    abstract public function getSlug();

    /**
     * Get path for the section based on the slug.
     * 
     * @param bool $absolute
     * @param string $slug
     * @return string
     */
    public function getStatisticsPath($absolute = false, $slug = null) {
        if ($slug === null) $slug = $this->getSlug();
        return $this->container->get('router')->generate('statistics_results', array('section_slug' => $slug), $absolute);
    }
    
    protected $prevSeason = null;
    
    /**
     * Get string description of the previous season (@see getPrevSeason()).
     * 
     * @return string
     */
    public function getPrevSeasonDesc() {
    	if ($this->prevSeason === null) $this->prevSeason = $this->getPrevSeason();
    	return $this->prevSeason->getDescription();
    }
    
    /**
     * Get the first previous season where there is an entry for particular section.
     * 
     * @return Season
     */
    abstract public function getPrevSeason();
    
    protected $prevLink = null;
    
    /**
     * Get link to stats for previous season.
     * 
     * @param bool $absolute
     * @return string
     */
    public function getPrevLink($absolute = false) {
        if ($this->getPrevSeason() == null) return null;
        $this->prevSeason = $this->getPrevSeason();
        $prevSlug = $this->getSlug($this->prevSeason);
        return $this->getStatisticsPath($absolute, $prevSlug);
    }

    protected $associationExamples = null;

    public function getAssociationExamples() {
        return $this->associationExamples;
    }

}

