<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AnketaBundle\Entity\TeachingAssociation;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DateTime;

class TeachingAssociationController extends Controller
{
    
    public function formAction($subject_code)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('AnketaBundle\Entity\Subject');
        $subject = $repo->findOneBy(array('code' => $subject_code));
        if ($subject === null) {
            throw new NotFoundHttpException('Chybny kod: ' . $subject_code);
        }
        
        return $this->render('AnketaBundle:TeachingAssociation:form.html.twig',
                array('subject'=>$subject));
    }
    
    public function processFormAction($subject_code)
    {
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');
        $subjectRepository = $em->getRepository('AnketaBundle\Entity\Subject');
        $seasonRepository = $em->getRepository('AnketaBundle\Entity\Season');
        
        $season = $seasonRepository->getActiveSeason(new DateTime());
        $subject = $subjectRepository->findOneBy(array('code' => $subject_code));
        if ($subject === null) {
            throw new NotFoundHttpException('Chybny kod: ' . $subject_code);
        }
        $security = $this->get('security.context');
        $user = $security->getToken()->getUser();
        $note = $request->request->get('note', '');
        
        // TODO(anty): toto sa nastavi, az ked budeme mat UI na vybratie ucitela zo zoznamu
        $teacher = null;

        $assoc = new TeachingAssociation($season, $subject, $teacher, $user, $note);
        $em->persist($assoc);
        $em->flush();

        $emailTpl = array(
                'subject' => $subject,
                'teacher' => $teacher,
                'note' => $note,
                'user' => $user);
        $sender = $this->container->getParameter('mail_sender');
        $to = $this->container->getParameter('mail_dest_new_teaching_association');
        $body = $this->renderView('AnketaBundle:TeachingAssociation:email.txt.twig', $emailTpl);

       $this->get('mailer'); // DO NOT DELETE THIS LINE
       // it autoloads required things before Swift_Message can be used

        $message = \Swift_Message::newInstance()
                        ->setSubject('FMFI ANKETA -- requested teacher')
                        ->setFrom($sender)
                        ->setTo($to)
                        ->setBody($body);
        $this->get('mailer')->send($message);
        
        $session = $this->get('session');
        $session->setFlash('success',
                'Ďakujeme za informáciu o chýbajúcom učiteľovi. ' .
                'V priebehu pár dní by mal pribudnúť, preto si nezabudnite ' .
                'otvoriť anketu znovu a ohodnotiť ho.');
        
        return new RedirectResponse($this->generateUrl(
                'answer_subject', array('code'=>$subject->getCode())));
    }
    
}
