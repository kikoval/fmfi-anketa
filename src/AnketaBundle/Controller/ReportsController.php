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

    public static function compareAverageEvaluation($teacher1, $teacher2)
{
    return $teacher1->evaluation[1] > $teacher2->evaluation[1];
}

    
    public function studyProgrammeAction($study_programme_id, $season_slug = null) {

               $em = $this->get('doctrine.orm.entity_manager');
        $security = $this->get('security.context');
        if (!$security->isGranted('ROLE_STUDY_PROGRAMME_REPORT')) {
            throw new AccessDeniedException();
        } 
    $season = $em->getRepository('AnketaBundle:Season')->findOneBy(array('slug' => $season_slug));
    if ($season === null) {
        throw new NotFoundHttpException();
    } 
    $teachers = $em->getRepository('AnketaBundle:Teacher')->getTeachersForStudyProgramme($study_programme_id, $season);
    foreach ($teachers as $teacher) {
        $teacher->subjects = $em->getRepository('AnketaBundle:Subject')->getSubjectsForTeacher($teacher, $season);
        $teacher->evaluation = $em->getRepository('AnketaBundle:Answer')->getAverageEvaluationForTeacher($teacher, $season);
    }
    usort($teachers, array('AnketaBundle\Controller\ReportsController','compareAverageEvaluation'));    
    
    $subjects = $em->getRepository('AnketaBundle:Subject')->getSubjectsForStudyProgramme($study_programme_id, $season);
    foreach ($subjects as $subject) {
        $subject->teacher = $em->getRepository('AnketaBundle:Teacher')->getTeachersForSubject($subject, $season);
        $subject->evaluation = $em->getRepository('AnketaBundle:Answer')->getAverageEvaluationForSubject($subject, $season);
    }
    usort($subjects, array('AnketaBundle\Controller\ReportsController','compareAverageEvaluation'));
    
    return $this->render('AnketaBundle:Reports:studyProgramme.html.twig', 
            array('subjects' => $subjects, 'teachers' => $teachers, 'season' => $season));
    }

    public function departmentAction($department_id, $season_slug = null) {
        $em = $this->get('doctrine.orm.entity_manager');
            $season = $em->getRepository('AnketaBundle:Season')->findOneBy(array('slug' => $season_slug));
    if ($season === null) {
        throw new NotFoundHttpException();
    }
        return $this->render('AnketaBundle:Reports:department.html.twig', array('season' => $season));
    }
    
    public function myReportsAction($season_slug = null) {
        $em = $this->get('doctrine.orm.entity_manager');
            $season = $em->getRepository('AnketaBundle:Season')->findOneBy(array('slug' => $season_slug));
    if ($season === null) {
        throw new NotFoundHttpException();
    }
        return $this->render('AnketaBundle:Reports:myReports.html.twig', array('season' => $season));
    }
}