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

    protected function __construct() {
    }

    public static function makeSubjectTeacherSection(ContainerInterface $container, Season $season, Subject $subject, Teacher $teacher) {
        $em = $container->get('doctrine.orm.entity_manager');
        if ($em->getRepository('AnketaBundle:TeachersSubjects')->findOneBy(array('teacher' => $teacher->getId(), 'subject' => $subject->getId(), 'season' => $season->getId())) === null) {
            throw new \Exception('Section not found: Teacher "'.$teacher->getId().'" doesn\'t teach subject "'.$subject->getId().'".');
        }
        $result = new StatisticsSection();
        $result->setContainer($container);
        $result->season = $season;
        $result->subject = $subject;
        $result->teacher = $teacher;
        $result->title = $subject->getCode() . ' ' . $subject->getName() . ' - ' . $teacher->getName();
        $result->questionsCategoryType = CategoryType::TEACHER_SUBJECT;
        $result->responsesQuery = array('season' => $season->getId(), 'subject' => $subject->getId(), 'teacher' => $teacher->getId(), 'studyProgram' => null);
        $result->statisticsRoute = 'results_subject_teacher';
        $result->statisticsRouteParameters =
                array('season_slug' => $season->getSlug(), 'subject_code' => $subject->getCode(), 'teacher_id' => $teacher->getId());
        $result->slug = $season->getSlug() . '/predmet/' . $subject->getCode() . '/ucitel/' . $teacher->getId();
        return $result;
    }

    public static function makeSubjectSection(ContainerInterface $container, Season $season, Subject $subject) {
        $result = new StatisticsSection();
        $result->setContainer($container);
        $result->season = $season;
        $result->subject = $subject;
        $result->title = $subject->getCode() . ' ' . $subject->getName();
        $result->questionsCategoryType = CategoryType::SUBJECT;
        $result->responsesQuery = array('season' => $season->getId(), 'subject' => $subject->getId(), 'teacher' => null, 'studyProgram' => null);
        $result->statisticsRoute = 'results_subject';
        $result->statisticsRouteParameters =
                array('season_slug' => $season->getSlug(), 'subject_code' => $subject->getCode());
        $result->slug = $season->getSlug() . '/predmet/' . $subject->getCode();
        return $result;
    }

    public static function makeGeneralSection(ContainerInterface $container, Season $season, Question $generalQuestion) {
        $result = new StatisticsSection();
        $result->setContainer($container);
        $result->season = $season;
        $result->generalQuestion = $generalQuestion;
        $result->title = $generalQuestion->getQuestion();
        $result->headingVisible = false;
        $result->responsesQuery = array('season' => $season->getId(), 'question' => $generalQuestion->getId());
        $result->statisticsRoute = 'statistics_results_general';
        $result->statisticsRouteParameters =
                array('season_slug' => $season->getSlug(), 'question_id' => $generalQuestion->getId());
        $result->slug = $season->getSlug() . '/vseobecne/' . $generalQuestion->getId();
        return $result;
    }

    public static function makeStudyProgramSection(ContainerInterface $container, Season $season, StudyProgram $studyProgram) {
        $result = new StatisticsSection();
        $result->setContainer($container);
        $result->season = $season;
        $result->studyProgram = $studyProgram;
        $result->title = $studyProgram->getCode() . ' ' . $studyProgram->getName();
        $result->questionsCategoryType = CategoryType::STUDY_PROGRAMME;
        $result->responsesQuery = array('season' => $season->getId(), 'studyProgram' => $studyProgram->getId(), 'teacher' => null, 'subject' => null);
        $result->statisticsRoute = 'statistics_study_program';
        $result->statisticsRouteParameters =
                array('season_slug' => $season->getSlug(), 'program_slug' => $studyProgram->getSlug());
        $result->slug = $season->getSlug() . '/program/' . $studyProgram->getSlug();
        return $result;
    }

    public static function getSectionOfAnswer(ContainerInterface $container, Answer $answer) {
        $category = $answer->getQuestion()->getCategory()->getType();
        if ($category == CategoryType::TEACHER_SUBJECT) return self::makeSubjectTeacherSection($container, $answer->getSeason(), $answer->getSubject(), $answer->getTeacher());
        if ($category == CategoryType::SUBJECT) return self::makeSubjectSection($container, $answer->getSeason(), $answer->getSubject());
        if ($category == CategoryType::GENERAL) return $result->makeGeneralSection($container, $answer->getSeason(), $answer->getQuestion());
        if ($category == CategoryType::STUDY_PROGRAMME) return $result->makeStudyProgramSection($container, $answer->getSeason(), $answer->getStudyProgram());
        throw new \Exception('Unknown category type');
    }

    public static function getSectionOfResponse(ContainerInterface $container, Response $response) {
        if ($response->getTeacher() !== null) return self::makeSubjectTeacherSection($container, $response->getSeason(), $response->getSubject(), $response->getTeacher());
        if ($response->getSubject() !== null) return self::makeSubjectSection($container, $response->getSeason(), $response->getSubject());
        if ($response->getQuestion() !== null) return self::makeGeneralSection($container, $response->getSeason(), $response->getQuestion());
        if ($response->getStudyProgram() !== null) return self::makeStudyProgramSection($container, $response->getSeason(), $response->getStudyProgram());
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
            return self::makeSubjectTeacherSection($container, $season, $subject, $teacher);
        }
        if (preg_match('@^predmet/([a-zA-Z0-9-_]+)$@', $slug, $matches)) {
            $subject = $em->getRepository('AnketaBundle:Subject')->findOneBy(array('code' => $matches[1]));
            if ($subject === null) {
                throw new \Exception('Section not found: Subject "'.$matches[1].'" not found.');
            }
            return self::makeSubjectSection($container, $season, $subject);
        }
        if (preg_match('@^vseobecne/(\d+)$@', $slug, $matches)) {
            $question = $em->find('AnketaBundle:Question', $matches[1]);
            if ($question === null) {
                throw new \Exception('Section not found: Question "'.$matches[1].'" not found.');
            }
            return self::makeGeneralSection($container, $season, $question);
        }
        if (preg_match('@^program/([a-zA-Z0-9-_]+)$@', $slug, $matches)) {
            $program = $em->getRepository('AnketaBundle:StudyProgram')->findOneBy(array('slug' => $matches[1]));
            if ($program === null) {
                throw new \Exception('Section not found: Program "'.$matches[1].'" not found.');
            }
            return self::makeStudyProgramSection($container, $season, $program);
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

