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
    $subjects = $em->getRepository('AnketaBundle:Subject')->getSubjectsForStudyProgramme($study_programme_id, $season);
    
        
    return $this->render('AnketaBundle:Reports:studyProgramme.html.twig', 
            array('subjects' => $subjects, 'teachers' => array(1, 2, 3)));
    }

    public function departmentAction($department_id, $season_slug = null) {
        return $this->render('AnketaBundle:Reports:department.html.twig');
    }
    
    public function myReportsAction($season_slug = null) {
        return $this->render('AnketaBundle:Reports:myReports.html.twig');
    }
}