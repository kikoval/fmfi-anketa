<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AnketaBundle\Entity\Season;
use AnketaBundle\Entity\Subject;
use AnketaBundle\Entity\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ResponseController extends Controller {
    
    public function newResponseAction($section_slug) {
        $section = StatisticsSection::getSectionFromSlug($this->container, $section_slug);

        $access = $this->get('anketa.access.statistics');
        if (!$access->canCreateResponse($section->getSeason())) throw new AccessDeniedException();
        $user = $access->getUser();
        
        $response = new Response();
        $response->setAuthorLogin($user->getUserName());
        $response->setAuthorText($user->getDisplayName());
        $response->setSeason($section->getSeason());
        $response->setTeacher($section->getTeacher());
        $response->setSubject($section->getSubject());
        $response->setStudyProgram($section->getStudyProgram());
        $response->setQuestion($section->getGeneralQuestion());
        
        return $this->updateResponse($response);
    }
    
    public function editResponseAction($response_id, $delete) {
        $em = $this->get('doctrine.orm.entity_manager');
        $response = $em->find('AnketaBundle:Response', $response_id);
        if ($response == null) {
            throw new NotFoundHttpException('Neznama odpoved: ' . $response_id);
        }

        return $this->updateResponse($response, $delete);
    }
    
    private function updateResponse(Response $response, $delete = false) {
        $em = $this->get('doctrine.orm.entity_manager');
        $request = $this->get('request');

        if (!$this->get('anketa.access.statistics')->canEditResponse($response)) throw new AccessDeniedException();

        $section = StatisticsSection::getSectionOfResponse($this->container, $response);
        
        if ($request->getMethod() == 'POST') {
            if (!$delete) {
                if ($request->get('text', '') === '') {
                    return new RedirectResponse($request->getRequestUri());
                }
                $response->setComment($request->get('text', ''));
                $response->setAssociation($request->get('association', ''));
                if ($response->getId() === null) {
                    $em->persist($response);
                }
                $em->flush();
                $this->get('session')->setFlash('success', 'Váš komentár bol uložený.');
                return new RedirectResponse($section->getStatisticsPath());
            }
            else {
                $em->remove($response);
                $em->flush();
                $this->get('session')->setFlash('success', 'Váš komentár bol zmazaný.');
                return new RedirectResponse($this->generateUrl('response'));
            }
        }
        else {
            $template = $delete ?
                'AnketaBundle:Response:delete.html.twig' :
                'AnketaBundle:Response:edit.html.twig';
            return $this->render($template, array(
                'section' => $section,
                'submitLink' => $request->getRequestUri(),
                'responseText' => $response->getComment(),
                'association' => $response->getAssociation(),
                'new' => $response->getId() === null,
                'responsePage' => null
            ));
        }
    }
    
    public function listMineAction($season_slug) {
        $em = $this->get('doctrine.orm.entity_manager');
        $access = $this->get('anketa.access.statistics');
        if (!$access->hasOwnResponses()) throw new AccessDeniedException();
        $user = $access->getUser();
        
        $seasonRepo = $em->getRepository('AnketaBundle\Entity\Season');
        $season = $seasonRepo->findOneBy(array('slug' => $season_slug));
        if ($season == null) {
            throw new NotFoundHttpException('Chybna sezona: ' . $season_slug);
        }
        
        $responseRepo = $em->getRepository('AnketaBundle:Response');
        $query = array('author_login' => $user->getUserName(), 'season' => $season->getId());
        $responses = $responseRepo->findBy($query);
        $processedResponses = array();
        foreach ($responses as $response) {
            $processedResponses[] = array(
                'response' => $response,
                'section' => StatisticsSection::getSectionOfResponse($this->container, $response)
            );
        }
        
        return $this->render('AnketaBundle:Response:list.html.twig',
                array('responses' => $processedResponses, 'season' => $season));
    }
    
}
