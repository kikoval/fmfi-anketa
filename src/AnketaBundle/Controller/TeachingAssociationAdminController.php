<?php

namespace AnketaBundle\Controller;

use Doctrine\DBAL\DBALException;

use AnketaBundle\Entity\TeachersSubjects;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\TeachingAssociation;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class TeachingAssociationAdminController extends Controller {

    public function preExecute() {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) { // ROLE_ADMIN
            throw new AccessDeniedException();
        }
    }

    // TODO pagination
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $active_season = $em->getRepository('AnketaBundle:Season')
                ->getActiveSeason();

        $tas = $em->getRepository('AnketaBundle:TeachingAssociation')
                ->findBy(array('season' => $active_season,
                               'completed' => false));

        return $this->render(
                        'AnketaBundle:TeachingAssociationAdmin:index.html.twig',
                        array('tas' => $tas));
    }

    public function processRequestAction() {
        $approve = $this->getRequest()->get('approve', null);
        if ($approve !== null) {
            return $this->addTeacherToSubject();
        }
        
        $mark_as_completed = $this->getRequest()->get('mark-as-completed', null);
        if ($mark_as_completed !== null) {
            return $this->markAsCompleted();
        }
    }
    
    public function addTeacherToSubject() {
        $ta_id = $this->getRequest()->get('ta_id', null);
        if ($ta_id == null) {
            return new Response('Required parameter "ta_id" is missing.', 400);
        }

        $em = $this->getDoctrine()->getManager();
        $ta = $em->getRepository('AnketaBundle:TeachingAssociation')
                ->find($ta_id);

        if ($ta !== null && $ta->getTeacher() !== null
                && $ta->getSubject() !== null && $ta->getSeason() !== null) {
            $teachersSubjects = new TeachersSubjects($ta->getTeacher(),
                    $ta->getSubject(), $ta->getSeason());

            $teachersSubjects->setLecturer($ta->getLecturer());
            $teachersSubjects->setTrainer($ta->getTrainer());
    
            $session = $this->get('session');
            
            try {
                $em->persist($teachersSubjects);
                $em->flush();
            } catch (DBALException $e) {
                // TODO check $e says entry is duplicated
                $session->getFlashBag()->add('warning', 'Učiteľ už je priradený k predmetu.');
                return $this->redirect($this->generateUrl(
                        'admin_teaching_associations'));
            }

            // TODO kontrola na uspesnosti vykonania predchadzajucej query?
            $ta->setCompleted(true);
            $em->persist($ta);
            $em->flush();
            
            $session->getFlashBag()->add('succcess', 'Učiteľ bol úspešne priradený k predmetu.');
            
            return $this->redirect($this->generateUrl(
                    'admin_teaching_associations'));
        }
    }
    
    public function markAsCompleted() {
        $ta_id = $this->getRequest()->get('ta_id', null);
        if ($ta_id == null) {
            return new Response('Required parameter "ta_id" is missing.', 400);
        }
        
        $em = $this->getDoctrine()->getManager();
        $ta = $em->getRepository('AnketaBundle:TeachingAssociation')
                ->find($ta_id);
        if ($ta !== null) {
            $ta->setCompleted(true);
            $em->persist($ta);
            $em->flush();
        }
        
        return $this->redirect($this->generateUrl(
                'admin_teaching_associations'));
    }
}
