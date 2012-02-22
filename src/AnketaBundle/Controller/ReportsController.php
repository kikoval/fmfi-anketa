<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AnketaBundle\Entity\Season;
use AnketaBundle\Entity\Subject;
use AnketaBundle\Entity\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AnketaBundle\Entity\Teacher;

class ReportsController extends Controller {

    public static function compareAverageEvaluation($entity1, $entity2) {
        return $entity1->evaluation[1] > $entity2->evaluation[1];
    }

    public function studyProgrammeAction($study_programme_slug, $season_slug = null) {

        $em = $this->get('doctrine.orm.entity_manager');
        $security = $this->get('security.context');
        //TODO uprsnit na konkretnE studijnE programy
        if (!$security->isGranted('ROLE_STUDY_PROGRAMME_REPORT') && !$security->isGranted('ROLE_ALL_REPORTS')) {
            throw new AccessDeniedException();
        }
        
        $season = $em->getRepository('AnketaBundle:Season')->findOneBy(array('slug' => $season_slug));
        if ($season === null) {
            throw new NotFoundHttpException();
        }

        $study_programme_id = $em->getRepository('AnketaBundle:StudyProgram')->findOneBy(array('slug' => $study_programme_slug));
        if ($study_programme_id === null) {
            throw new NotFoundHttpException();
        }
        
        $teachers = $em->getRepository('AnketaBundle:Teacher')->getTeachersForStudyProgramme($study_programme_id, $season);
        foreach ($teachers as $teacher) {
            $teacher->subjects = $em->getRepository('AnketaBundle:Subject')->getSubjectsForTeacherWithAnswers($teacher, $season);
            $teacher->evaluation = $em->getRepository('AnketaBundle:Answer')->getAverageEvaluationForTeacher($teacher, $season);
        }

        usort($teachers, array('AnketaBundle\Controller\ReportsController', 'compareAverageEvaluation'));

        $subjects = $em->getRepository('AnketaBundle:Subject')->getSubjectsForStudyProgramme($study_programme_id, $season);
        foreach ($subjects as $subject) {
            $subject->teacher = $em->getRepository('AnketaBundle:Teacher')->getTeachersForSubjectWithAnswers($subject, $season);
            $subject->evaluation = $em->getRepository('AnketaBundle:Answer')->getAverageEvaluationForSubject($subject, $season);
        }

        usort($subjects, array('AnketaBundle\Controller\ReportsController', 'compareAverageEvaluation'));

        return $this->render('AnketaBundle:Reports:report.html.twig', array('subjects' => $subjects, 'teachers' => $teachers, 'season' => $season, 'title' => "Študijný program ". $study_programme_id->getName()));
    }

    public function departmentAction($department_slug, $season_slug = null) {

        $em = $this->get('doctrine.orm.entity_manager');
        $security = $this->get('security.context');
        //TODO uprsnit na konkretnU katedrU
        if (!$security->isGranted('ROLE_STUDY_DEPARTMENT_REPORT') && !$security->isGranted('ROLE_ALL_REPORTS')) {
            throw new AccessDeniedException();
        }
        
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
        
        $teachers = $em->getRepository('AnketaBundle:Teacher')->getTeachersForDepartment($department, $season);
        foreach ($teachers as $teacher) {
            $teacher->subjects = $em->getRepository('AnketaBundle:Subject')->getSubjectsForTeacherWithAnswers($teacher, $season);
            $teacher->evaluation = $em->getRepository('AnketaBundle:Answer')->getAverageEvaluationForTeacher($teacher, $season);
        }

        usort($teachers, array('AnketaBundle\Controller\ReportsController', 'compareAverageEvaluation'));

        $subjects = $em->getRepository('AnketaBundle:Subject')->getSubjectsForDepartment($department, $season);
        foreach ($subjects as $subject) {
            $subject->teacher = $em->getRepository('AnketaBundle:Teacher')->getTeachersForSubjectWithAnswers($subject, $season);
            $subject->evaluation = $em->getRepository('AnketaBundle:Answer')->getAverageEvaluationForSubject($subject, $season);
        }

        usort($subjects, array('AnketaBundle\Controller\ReportsController', 'compareAverageEvaluation'));

        return $this->render('AnketaBundle:Reports:report.html.twig', array('subjects' => $subjects, 'teachers' => $teachers, 'season' => $season, 'title' => $department->getName()));
    }

    public function myReportsAction($season_slug = null) {
        $em = $this->get('doctrine.orm.entity_manager');
        $security = $this->get('security.context');
        $season = $em->getRepository('AnketaBundle:Season')->findOneBy(array('slug' => $season_slug));
        if ($season === null) {
            throw new NotFoundHttpException();
        }
        
        $user = $security->getToken()->getUser();
        
        // katedry
        $deptRepository = $em->getRepository('AnketaBundle:Department');
        if ($security->isGranted('ROLE_ALL_REPORTS')) {
            $departments = $deptRepository->findBy(array(), array('name' => 'ASC'));
        }
        else if ($security->isGranted('ROLE_DEPARTMENT_REPORT')) {
            $departments = $deptRepository->findByUser($user, $season);
        }
        else {
            $departments = null;
        }
        
        // studijne programy
        $spRepository = $em->getRepository('AnketaBundle:StudyProgram');
        if ($security->isGranted('ROLE_ALL_REPORTS')) {
            $studyPrograms = $spRepository->getAllWithAnswers($season, true);
        }
        else if ($security->isGranted('ROLE_STUDY_PROGRAMME_REPORT')) {
            $studyPrograms = $spRepository->findByReportsUser($user, $season);
        }
        else {
            $studyPrograms = null;
        }
        
        return $this->render('AnketaBundle:Reports:myReports.html.twig',
                array('season' => $season, 'studyPrograms' => $studyPrograms,
                    'departments' => $departments));
    }

}