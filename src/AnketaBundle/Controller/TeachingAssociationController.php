<?php

namespace AnketaBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use AnketaBundle\Integration\LDAPTeacherSearch;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AnketaBundle\Entity\TeachingAssociation;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TeachingAssociationController extends Controller
{

    public function preExecute() {
        if (!$this->get('anketa.access.hlasovanie')->userCanVote()) throw new AccessDeniedException();
        
        $this->ldapSearch = new LDAPTeacherSearch($this->container->get('anketa.ldap_retriever'),
        										  $this->container->getParameter('org_unit'));
    }

    public function formAction($subject_slug)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('AnketaBundle\Entity\Subject');
        $subject = $repo->findOneBy(array('slug' => $subject_slug));
        if ($subject === null) {
            throw new NotFoundHttpException('Chybny slug predmetu: ' . $subject_slug);
        }

        return $this->render('AnketaBundle:TeachingAssociation:form.html.twig',
                array('subject'=>$subject));
    }

    public function processFormAction($subject_slug)
    {
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');
        $subjectRepository = $em->getRepository('AnketaBundle\Entity\Subject');
        $seasonRepository = $em->getRepository('AnketaBundle\Entity\Season');
        $userRepository = $em->getRepository('AnketaBundle\Entity\User');

        $season = $seasonRepository->getActiveSeason();
        $subject = $subjectRepository->findOneBy(array('slug' => $subject_slug));
        if ($subject === null) {
            throw new NotFoundHttpException('Chybny slug predmetu: ' . $subject_slug);
        }
        $security = $this->get('security.context');
        $user = $security->getToken()->getUser();
        $note = $request->request->get('note', '');
        $is_lecturer = $request->request->get('teacher', null);
        $is_trainer = $request->request->get('teacher-assistant', null);

        $teacher_login = $request->get('teacher-login', null);
        $teacher = $userRepository->findOneBy(array('login' => $teacher_login));

        $assoc = new TeachingAssociation($season, $subject, $teacher, $user, $note, $is_lecturer, $is_trainer);
        $em->persist($assoc);
        $em->flush();

        $emailTpl = array(
                'subject' => $subject,
                'teacher' => $teacher,
        		'is_lecturer' => $is_lecturer,
        		'is_trainer' => $is_trainer,
                'note' => $note,
                'user' => $user);
        $sender = $this->container->getParameter('mail_sender');
        $to = $this->container->getParameter('mail_dest_new_teaching_association');
        $body = $this->renderView('AnketaBundle:TeachingAssociation:email.txt.twig', $emailTpl);
        $skratkaFakulty = $this->container->getParameter('skratka_fakulty');

       $this->get('mailer'); // DO NOT DELETE THIS LINE
       // it autoloads required things before Swift_Message can be used

        $message = \Swift_Message::newInstance()
                        ->setSubject($skratkaFakulty . ' ANKETA -- requested teacher')
                        ->setFrom($sender)
                        ->setTo($to)
                        ->setBody($body);
        $this->get('mailer')->send($message);

        $session = $this->get('session');
        $session->setFlash('success',
                'Ďakujeme za informáciu. ' .
                'V priebehu pár dní by mala byť spracovaná, preto si nezabudnite ' .
                'otvoriť anketu znovu a ohodnotiť prípadných pridaných učiteľov.');

        return new RedirectResponse($this->generateUrl(
                'answer_subject', array('subject_slug'=>$subject->getSlug())));
    }

}
