<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ReportsController extends Controller {

    public static function compareAverageEvaluation($entity1, $entity2) {
        return $entity1->evaluation[1] > $entity2->evaluation[1];
    }

    public function makeReport($season, $teachers, $subjects, $templateParams) {
        $em = $this->get('doctrine.orm.entity_manager');

        foreach ($teachers as $teacher) {
            $teacher->subjects = $em->getRepository('AnketaBundle:Subject')->getSubjectsForTeacherWithAnswersAboutTeacher($teacher, $season);
            $teacher->evaluation = $em->getRepository('AnketaBundle:Answer')->getAverageEvaluationForTeacher($teacher, $season);
            $teacher->links = array();
            foreach ($teacher->subjects as $subject) {
                $teacherStatsSection = new StatisticsTeacherSubjectSection($this->container, $season, $subject, $teacher);
                $teacher->links[$subject->getId()] = $teacherStatsSection->getStatisticsPath();
            }
        }
        usort($teachers, array('AnketaBundle\Controller\ReportsController', 'compareAverageEvaluation'));

        foreach ($subjects as $subject) {
            $subject->teacher = $em->getRepository('AnketaBundle:User')->getTeachersForSubjectWithAnswers($subject, $season);
            $subject->evaluation = $em->getRepository('AnketaBundle:Answer')->getAverageEvaluationForSubject($subject, $season);
            $subjectStatsSection = new StatisticsSubjectSection($this->container, $season, $subject);
            $subject->link = $subjectStatsSection->getStatisticsPath();
            $subject->links = array();
            foreach ($subject->teacher as $teacher) {
                $teacherStatsSection = new StatisticsTeacherSubjectSection($this->container, $season, $subject, $teacher);
                $subject->links[$teacher->getId()] = $teacherStatsSection->getStatisticsPath();
            }
        }
        usort($subjects, array('AnketaBundle\Controller\ReportsController', 'compareAverageEvaluation'));

        $templateParams['teachers'] = $teachers;
        $templateParams['subjects'] = $subjects;
        $templateParams['season'] = $season;
        return $this->render('AnketaBundle:Reports:report.html.twig', $templateParams);
    }

    public function studyProgrammeAction($study_programme_slug, $season_slug = null) {

        $em = $this->get('doctrine.orm.entity_manager');

        $season = $em->getRepository('AnketaBundle:Season')->findOneBy(array('slug' => $season_slug));
        if ($season === null) {
            throw new NotFoundHttpException();
        }

        $studyProgramme = $em->getRepository('AnketaBundle:StudyProgram')->findOneBy(array('slug' => $study_programme_slug));
        if ($studyProgramme === null) {
            throw new NotFoundHttpException();
        }

        // TODO: don't get the full list, only check if we can access this item
        if (!in_array($studyProgramme, $this->get('anketa.access.statistics')->getStudyProgrammeReports($season))) {
            throw new AccessDeniedException();
        }

        return $this->makeReport($season,
            $em->getRepository('AnketaBundle:User')->getTeachersForStudyProgramme($studyProgramme, $season),
            $em->getRepository('AnketaBundle:Subject')->getSubjectsForStudyProgramme($studyProgramme, $season),
            array('title' => $studyProgramme->getCode() . ' ' . $studyProgramme->getName(),
                'studyProgrammeLink' => StatisticsSection::makeStudyProgramSection($this->container, $season, $studyProgramme)->getStatisticsPath(),
                'studyProgramme' => $studyProgramme));
    }

    public function departmentAction($department_slug, $season_slug = null) {

        $em = $this->get('doctrine.orm.entity_manager');

        $season = $em->getRepository('AnketaBundle:Season')->findOneBy(array('slug' => $season_slug));
        if ($season === null) {
            throw new NotFoundHttpException();
        }

        // TODO: create separate slug column in entity
        $department_code = str_replace('-', '.', $department_slug);
        $department = $em->getRepository('AnketaBundle:Department')->findOneBy(array('code' => $department_code));
        if ($department === null) {
            throw new NotFoundHttpException();
        }

        // TODO: don't get the full list, only check if we can access this item
        if (!in_array($department, $this->get('anketa.access.statistics')->getDepartmentReports($season))) {
            throw new AccessDeniedException();
        }

        return $this->makeReport($season,
            $em->getRepository('AnketaBundle:User')->getTeachersForDepartment($department, $season),
            $em->getRepository('AnketaBundle:Subject')->getSubjectsForDepartment($department, $season),
            array('title' => $department->getName()));
    }

    public function myReportsAction($season_slug = null) {
        $em = $this->get('doctrine.orm.entity_manager');
        $access = $this->get('anketa.access.statistics');
        $season = $em->getRepository('AnketaBundle:Season')->findOneBy(array('slug' => $season_slug));
        if ($season === null) {
            throw new NotFoundHttpException();
        }

        if (!$access->canSeeResults($season)) throw new AccessDeniedException();
        if (!$access->hasReports()) throw new AccessDeniedException();

        $items = array();

        $departments = $access->getDepartmentReports($season);
        if (count($departments)) {
            $links = array();
            foreach ($departments as $department) {
                $links[$department->getName()] =
                    $this->generateUrl('report_department', array('season_slug' => $season->getSlug(), 'department_slug' => $department->getSlug()));
            }
            $items['Katedry'] = $links;
        }

        $studyPrograms = $access->getStudyProgrammeReports($season);
        if (count($studyPrograms)) {
            $links = array();
            foreach ($studyPrograms as $studyProgram) {
                $links[$studyProgram->getName() . ' (' . $studyProgram->getCode() . ')'] =
                    $this->generateUrl('report_study_programme', array('season_slug' => $season->getSlug(), 'study_programme_slug' => $studyProgram->getSlug()));
            }
            $items['Študijné programy'] = $links;
        }

        $templateParams = array();
        $templateParams['title'] = 'Moje reporty';
        $templateParams['activeMenuItems'] = array($season->getId(), 'my_reports');
        $templateParams['items'] = $items;
        return $this->render('AnketaBundle:Statistics:listing.html.twig', $templateParams);
    }

}
