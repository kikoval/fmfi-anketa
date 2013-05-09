<?php

namespace AnketaBundle\Controller;

use AnketaBundle\Entity\UserSeason;

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
    // TODO group by teacher id, zobrazit pocet rovnakych cez COUNT()
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

    // TODO send email to the user that reported the issue (and display a message about it)
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
    
    public function sendEmailAfterApproval(User $user) {
        
        $to = $user->getLogin().'@uniba.sk';
        $sender = $this->container->getParameter('mail_sender');
        $body = $this->renderView('AnketaBundle:TeachingAssociationAdmin:email.txt.twig');
        $skratkaFakulty = $this->container->getParameter('skratka_fakulty');
        
        $this->get('mailer'); // DO NOT DELETE THIS LINE
        // it autoloads required things before Swift_Message can be used
        
        $message = \Swift_Message::newInstance()
        ->setSubject($skratkaFakulty . ' ANKETA -- requested teacher')
        ->setFrom($sender)
        ->setTo($to)
        ->setBody($body);
        $this->get('mailer')->send($message);
        
        $this->get('session')->getFlashBag()->add('success',
                'Bol poslaný email o úspešnom vyriešení požiadavky používateľovi
                , ktorý ju nahlásil.');
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
            
            // create and store UserSeason
            $userSeason = new UserSeason();
            $userSeason->setIsStudent(false);
            $userSeason->setIsTeacher(true);
            $userSeason->setSeason($ta->getSeason());
            $userSeason->setUser($ta->getTeacher());
            
            $em->persist($userSeason);
            $em->flush();
            
            // create and store TeachersSubjects
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
                $session->getFlashBag()->add('error', 'Učiteľ už je priradený k predmetu.');
                return $this->redirect($this->generateUrl(
                        'admin_teaching_associations'));
            }

            // TODO kontrola na uspesnosti vykonania predchadzajucej query?
            $ta->setCompleted(true);
            $em->persist($ta);
            $em->flush();
            
            if ($ta->getRequestedBy() !== null)
                $this->sendEmailAfterApproval($ta->getRequestedBy());
            
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
            
            if ($ta->getRequestedBy() !== null)
                $this->sendEmailAfterApproval($ta->getRequestedBy());
        }
        
        return $this->redirect($this->generateUrl(
                'admin_teaching_associations'));
    }
}
