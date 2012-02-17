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
use AnketaBundle\Entity\Teacher;
use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\StudyProgram;
use AnketaBundle\Entity\Answer;
use AnketaBundle\Entity\Response;
use AnketaBundle\Entity\CategoryType;

class StatisticsSection extends ContainerAware {

    ///// I. the interesting part: various constructors

    protected function __construct(ContainerInterface $container, Season $season) {
        $this->setContainer($container);
        $this->season = $season;
    }

    protected function setSubjectTeacher(Subject $subject, Teacher $teacher) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        if ($em->getRepository('AnketaBundle:TeachersSubjects')->findOneBy(array('teacher' => $teacher->getId(), 'subject' => $subject->getId(), 'season' => $this->season->getId())) === null) {
            throw new \Exception('Section not found: Teacher "'.$teacher->getId().'" doesn\'t teach subject "'.$subject->getId().'".');
        }
        $this->subject = $subject;
        $this->teacher = $teacher;
        $this->title = $subject->getCode() . ' ' . $subject->getName() . ' - ' . $teacher->getName();
        $this->questionsCategoryType = CategoryType::TEACHER_SUBJECT;
        $this->responsesQuery = array('season' => $this->season->getId(), 'subject' => $subject->getId(), 'teacher' => $teacher->getId(), 'studyProgram' => null);
        $this->statisticsRoute = 'results_subject_teacher';
        $this->statisticsRouteParameters =
                array('season_slug' => $this->season->getSlug(), 'subject_code' => $subject->getCode(), 'teacher_id' => $teacher->getId());
        $this->slug = $this->season->getSlug() . '/predmet/' . $subject->getCode() . '/ucitel/' . $teacher->getId();
        return $this;
    }

    protected function setSubject(Subject $subject) {
        $this->subject = $subject;
        $this->title = $subject->getCode() . ' ' . $subject->getName();
        $this->questionsCategoryType = CategoryType::SUBJECT;
        $this->responsesQuery = array('season' => $this->season->getId(), 'subject' => $subject->getId(), 'teacher' => null, 'studyProgram' => null);
        $this->statisticsRoute = 'results_subject';
        $this->statisticsRouteParameters =
                array('season_slug' => $this->season->getSlug(), 'subject_code' => $subject->getCode());
        $this->slug = $this->season->getSlug() . '/predmet/' . $subject->getCode();
        return $this;
    }

    protected function setGeneralQuestion(Question $generalQuestion) {
        $this->generalQuestion = $generalQuestion;
        $this->title = $generalQuestion->getTitle();
        $this->headingVisible = false;
        $this->responsesQuery = array('season' => $this->season->getId(), 'question' => $question->getId());
        $this->statisticsRoute = 'statistics_results_general';
        $this->statisticsRouteParameters =
                array('season_slug' => $this->season->getSlug(), 'question_id' => $generalQuestion->getId());
        $this->slug = $this->season->getSlug() . '/vseobecne/' . $question->getId();
        return $this;
    }

    protected function setStudyProgram(StudyProgram $studyProgram) {
        $this->studyProgram = $studyProgram;
        $this->title = $studyProgram->getCode() . ' ' . $studyProgram->getName();
        $this->questionsCategoryType = CategoryType::STUDY_PROGRAMME;
        $this->responsesQuery = array('season' => $this->season->getId(), 'studyProgram' => $studyProgram->getId(), 'teacher' => null, 'subject' => null);
        $this->statisticsRoute = 'statistics_study_program';
        $this->statisticsRouteParameters =
                array('season_slug' => $this->season->getSlug(), 'program_slug' => $studyProgram->getSlug());
        $this->slug = $this->season->getSlug() . '/program/' . $studyProgram->getSlug();
        return $this;
    }

    public static function getSectionOfAnswer(ContainerInterface $container, Answer $answer) {
        $result = new StatisticsSection($container, $answer->getSeason());
        $category = $answer->getQuestion()->getCategory()->getType();
        if ($category == CategoryType::TEACHER_SUBJECT) return $result->setSubjectTeacher($answer->getSubject(), $answer->getTeacher());
        if ($category == CategoryType::SUBJECT) return $result->setSubject($answer->getSubject());
        if ($category == CategoryType::GENERAL) return $result->setGeneralQuestion($answer->getQuestion());
        if ($category == CategoryType::STUDY_PROGRAMME) return $result->setStudyProgram($answer->getStudyProgram());
        throw new \Exception('Unknown category type');
    }

    public static function getSectionOfResponse(ContainerInterface $container, Response $response) {
        $result = new StatisticsSection($container, $response->getSeason());
        if ($response->getTeacher() !== null) return $result->setSubjectTeacher($response->getSubject(), $response->getTeacher());
        if ($response->getSubject() !== null) return $result->setSubject($response->getSubject());
        if ($response->getQuestion() !== null) return $result->setGeneralQuestion($response->getQuestion());
        if ($response->getStudyProgram() !== null) return $result->setStudyProgram($response->getStudyProgram());
        throw new \Exception('Unknown type of response');
    }

    // TODO: mozno nie vracat null ale hadzat rozne exceptiony, nech sa da zistit co sa stalo
    public static function getSectionFromSlug(ContainerInterface $container, $slug) {
        $em = $container->get('doctrine.orm.entity_manager');
        if (!preg_match('@^([a-z0-9-]+)/(.*)$@', $slug, $matches)) {
            throw new \Exception('Section not found: Section slug doesn\'t start with season slug.');
        }
        $season = $em->getRepository('AnketaBundle:Season')->findOneBy(array('slug' => $matches[1]));
        if ($season === null) {
            throw new \Exception('Section not found: Season "'.$matches[1].'" not found.');
        }
        $result = new StatisticsSection($container, $season);
        $slug = $matches[2];
        if (preg_match('@^predmet/([a-zA-Z0-9-_]+)/ucitel/(\d+)$@', $slug, $matches)) {
            $subject = $em->getRepository('AnketaBundle:Subject')->findOneBy(array('code' => $matches[1]));
            if ($subject === null) {
                throw new \Exception('Section not found: Subject "'.$matches[1].'" not found.');
            }
            $teacher = $em->find('AnketaBundle:Teacher', $matches[2]);
            if ($teacher === null) {
                throw new \Exception('Section not found: Teacher "'.$matches[2].'" not found.');
            }
            return $result->setSubjectTeacher($subject, $teacher);
        }
        if (preg_match('@^predmet/([a-zA-Z0-9-_]+)$@', $slug, $matches)) {
            $subject = $em->getRepository('AnketaBundle:Subject')->findOneBy(array('code' => $matches[1]));
            if ($subject === null) {
                throw new \Exception('Section not found: Subject "'.$matches[1].'" not found.');
            }
            return $result->setSubject($subject);
        }
        if (preg_match('@^vseobecne/(\d+)$@', $slug, $matches)) {
            $question = $em->find('AnketaBundle:Question', $matches[1]);
            if ($question === null) {
                throw new \Exception('Section not found: Question "'.$matches[1].'" not found.');
            }
            return $result->setGeneralQuestion($question);
        }
        if (preg_match('@^program/([a-zA-Z0-9-_]+)$@', $slug, $matches)) {
            $program = $em->getRepository('AnketaBundle:StudyProgram')->findOneBy(array('slug' => $matches[1]));
            if ($program === null) {
                throw new \Exception('Section not found: Program "'.$matches[1].'" not found.');
            }
            return $result->setStudyProgram($program);
        }
        throw new \Exception('Section not found: Bad section slug format.');
    }

    ///// II. the boring part: instance variables and their accessors

    private $season = null;

    public function getSeason() {
        return $this->season;
    }

    private $subject = null;

    public function getSubject() {
        return $this->subject;
    }

    private $teacher = null;

    public function getTeacher() {
        return $this->teacher;
    }

    private $generalQuestion = null;

    public function getGeneralQuestion() {
        return $this->generalQuestion;
    }

    private $studyProgram = null;

    public function getStudyProgram() {
        return $this->studyProgram;
    }

    private $title = null;

    public function getTitle() {
        return $this->title;
    }

    private $headingVisible = true;

    public function getHeadingVisible() {
        return $this->headingVisible;
    }

    // TODO public function getQuestionsAndAnswers() or something like that

    private $responsesQuery = null;

    public function getResponses() {
        $em = $this->container->get('doctrine.orm.entity_manager');
        return $em->getRepository('AnketaBundle:Response')->findBy($this->responsesQuery);
    }

    // TODO ak zrefaktorujeme results, aby vsetky isli cez slug, toto budeme moct vyhodit.
    private $statisticsRoute = null;
    private $statisticsRouteParameters = null;

    public function getStatisticsPath($absolute = false) {
        return $this->container->get('router')->generate($this->statisticsRoute, $this->statisticsRouteParameters, $absolute);
    }

    private $slug = null;

    public function getSlug() {
        return $this->slug;
    }

}

