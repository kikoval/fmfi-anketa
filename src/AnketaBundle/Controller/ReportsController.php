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
        return $this->render('AnketaBundle:Reports:studyProgramme.html.twig', 
            array('subjects' => array(1, 2, 3, 4), 'teachers' => array(1, 2, 3)));
    }

    public function departmentAction($department_id, $season_slug = null) {
        return $this->render('AnketaBundle:Reports:department.html.twig');
    }
    
    public function allAction($season_slug = null) {
        return $this->render('AnketaBundle:Reports:all.html.twig');
    }
}