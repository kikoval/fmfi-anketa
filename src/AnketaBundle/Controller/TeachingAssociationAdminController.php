<?php

/**
 * @copyright Copyright (c) 2013 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Controller
 *
 */

namespace AnketaBundle\Controller;

use Doctrine\DBAL\DBALException;

use AnketaBundle\Entity\TeachersSubjects;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\TeachingAssociation;
use AnketaBundle\Entity\UserSeason;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class TeachingAssociationAdminController extends Controller {

    // TODO protect /admin at one place
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

    /**
     * Processes POST requests from forms.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse>
     */
    public function processRequestAction() {
        // check if the record exists
        $ta_id = $this->getRequest()->get('ta_id', null);
        if ($ta_id == null) {
            return new Response('Required parameter "ta_id" is missing.', 400);
        }
        $em = $this->getDoctrine()->getManager();
        $ta = $em->getRepository('AnketaBundle:TeachingAssociation')
                ->find($ta_id);
        if ($ta == null) {
            return new Response('Record was not found.', 400);
        }
        
        $approve = $this->getRequest()->get('approve', null);
        if ($approve !== null) {
            return $this->addTeacherToSubject($ta);
        }
        
        $mark_as_completed = $this->getRequest()->get('mark-as-completed', null);
        if ($mark_as_completed !== null) {
            return $this->markAsCompleted($ta);
        }
        
        $delete = $this->getRequest()->get('delete', null);
        if ($delete !== null) {
            $em->remove($ta);
            $em->flush();
            
            $this->get('session')->getFlashBag()
                    ->add('succcess', 'Záznam bol úspešne zmazaný.');
        }
        
        return $this->redirect($this->generateUrl(
                'admin_teaching_associations'));
    }
    
    /**
     * Links the teacher with the subject as reported in particular
     * TeachingAssociation.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function addTeacherToSubject(TeachingAssociation $ta) {
        $em = $this->getDoctrine()->getManager();

        if ($ta->getTeacher() !== null && $ta->getSubject() !== null
                && $ta->getSeason() !== null) {
            
            // TODO check for duplication
            $userSeason = new UserSeason();
            $userSeason->setIsStudent(false);
            $userSeason->setIsTeacher(true);
            $userSeason->setSeason($ta->getSeason());
            $userSeason->setUser($ta->getTeacher());
            
            $em->persist($userSeason);
            $em->flush();
            
            // link the teacher with the subject
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
            
            $session->getFlashBag()->add('succcess', 'Učiteľ bol úspešne priradený k predmetu.');
            
            return $this->redirect($this->generateUrl(
                    'admin_teaching_associations'));
        }
    }
    
    /**
     * Marks an request from TeachingAssociation as completed and sends email
     * to the user who requested it.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function markAsCompleted(TeachingAssociation $ta) {
        $em = $this->getDoctrine()->getManager();
        
        $ta->setCompleted(true);
        $em->persist($ta);
        $em->flush();
        
        if ($ta->getRequestedBy() !== null)
            $this->sendEmailAfterApproval($ta->getRequestedBy());

        return $this->redirect($this->generateUrl(
                'admin_teaching_associations'));
    }
    
    /**
     * Sends an email to the user about the successfull completion of his/her
     * request.
     *
     * @param User $user
     */
    private function sendEmailAfterApproval(User $user) {
        $to = $user->getLogin().'@uniba.sk';
        $sender = $this->container->getParameter('mail_sender');
        $body = $this->renderView('AnketaBundle:TeachingAssociationAdmin:email.txt.twig');
        $skratkaFakulty = $this->container->getParameter('skratka_fakulty');
    
        $this->get('mailer'); // DO NOT DELETE THIS LINE
        // it autoloads required things before Swift_Message can be used
    
        $message = \Swift_Message::newInstance()
        ->setSubject($skratkaFakulty . ' ANKETA -- requested teacher completed')
        ->setFrom($sender)
        ->setTo($to)
        ->setBody($body);
        $this->get('mailer')->send($message);
    
        $this->get('session')->getFlashBag()->add('success',
                'Bol poslaný email o úspešnom vyriešení požiadavky používateľovi
                , ktorý ju nahlásil.');
    }
}
