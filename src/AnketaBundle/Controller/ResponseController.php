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

class ResponseController extends Controller {
    
    public function newResponseAction($season_slug) {
        $em = $this->get('doctrine.orm.entity_manager');
        $security = $this->get('security.context');
        if (!$security->isGranted('ROLE_TEACHER')) {
            throw new AccessDeniedException();
        }
        $user = $security->getToken()->getUser();
        $teacherRepo = $em->getRepository('AnketaBundle\Entity\Teacher');
        $currentTeacher = $teacherRepo->findOneBy(array('login' => $user->getUserName()));
        
        $seasonRepo = $em->getRepository('AnketaBundle\Entity\Season');
        $season = $seasonRepo->findOneBy(array('slug' => $season_slug));
        if ($season == null) {
            throw new NotFoundHttpException('Chybna sezona: ' . $season_slug);
        }
        
        $request = $this->get('request');
        
        $subject_code = $request->get('subject_code');
        $subject = null;
        
        if ($subject_code !== null) {
            $subjectRepo = $em->getRepository('AnketaBundle\Entity\Subject');
            $subject = $subjectRepo->findOneBy(array('code' => $subject_code));
            if ($subject === null) {
                throw new NotFoundHttpException('Predmet nenajdeny');
            }
        }
        
        if ($subject === null) {
            // Ine veci ako predmet a predmet/ucitel su zatial neimplementovane
            throw new NotFoundHttpException();
        }
        
        $teacher_id = $request->get('teacher_id');
        $teacher = null;
        if ($teacher_id !== null) {
            $teacher = $teacherRepo->findOneBy(array('id' => $teacher_id));
            if ($teacher === null) {
                throw new NotFoundHttpException('Ucitel nenajdeny');
            }
        }
        
        $response = new Response();
        $response->setAuthorLogin($user->getUserName());
        $response->setAuthorText($user->getDisplayName());
        $response->setSubject($subject);
        $response->setTeacher($teacher);
        $response->setSeason($season);
        
        return $this->updateResponse($response, $currentTeacher);
    }
    
    public function editResponseAction($response_id) {
        $em = $this->get('doctrine.orm.entity_manager');
        $security = $this->get('security.context');
        if (!$security->isGranted('ROLE_TEACHER')) {
            throw new AccessDeniedException();
        }
        $user = $security->getToken()->getUser();
        $teacherRepo = $em->getRepository('AnketaBundle\Entity\Teacher');
        $currentTeacher = $teacherRepo->findOneBy(array('login' => $user->getUserName()));
        
        $responseRepo = $em->getRepository('AnketaBundle\Entity\Response');
        $response = $responseRepo->findOneBy(array('id' => $response_id));
        if ($response == null) {
            throw new NotFoundHttpException('Neznama odpoved: ' . $response_id);
        }
        
        return $this->updateResponse($response, $currentTeacher);
    }
    
    private function updateResponse(Response $response, Teacher $currentTeacher = null) {
        $em = $this->get('doctrine.orm.entity_manager');
        $request = $this->get('request');
        
        $teacher = $response->getTeacher();
        $subject = $response->getSubject();
        $season = $response->getSeason();
        
        if ($teacher !== null && $subject === null) {
            throw new NotFoundHttpException('Neznama kategoria');
        }
        
        $tsRepo = $em->getRepository('AnketaBundle\Entity\TeachersSubjects');
        if ($teacher !== null) {
            // Skontrolujeme, ci moze pridat novy response ako $teacher
            if ($teacher->getId() !== $currentTeacher->getId()) {
                throw new AccessDeniedException();
            }
            // Skontrolujeme, ci $teacher uci $subject
            if (!$tsRepo->teaches($teacher, $subject, $season)) {
                throw new NotFoundHttpException('Zla kombinacia vyucby');
            }
            $params = array('subject_code' => $subject->getCode(), 'teacher_id' => $teacher->getId(),
                        'season_slug' => $season->getSlug());
            $resultsLink = $this->generateUrl('results_subject_teacher', $params);
        }
        else {
            // Skontrolujeme, ci moze pridat response pre dany predmet
            if (!$tsRepo->teaches($currentTeacher, $subject, $season)) {
                throw new AccessDeniedException();
            }
            $params = array('subject_code' => $subject->getCode(), 'season_slug' => $season->getSlug());
            $resultsLink = $this->generateUrl('results_subject', $params);
        }
        
        if ($response->getId() === null) {
            $submitLink = $this->generateUrl('response_new', $params);
        }
        else {
            $submitLink = $this->generateUrl('response_edit', array('response_id' => $response->getId()));
        }
        
        if ($request->getMethod() == 'POST') {
            $responseText = $request->get('text', '');
            if ($responseText !== '') {
                $response->setComment($responseText);
                if ($response->getId() === null) {
                    $em->persist($response);
                }
                $em->flush();
                $session = $this->get('session');
                $session->setFlash('success',
                    'Vaša odpoveď bola uložená');
                return new RedirectResponse($resultsLink);
            }
        }
        else {
            $responseText = $response->getComment();
        }
        
        return $this->render('AnketaBundle:Response:edit.html.twig',
                array('subject' => $subject, 'teacher' => $teacher,
                    'submitLink' => $submitLink, 'responseText' => $responseText));
    }
    
}