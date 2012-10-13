<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ApiController extends Controller {

    public function resultsAction($section_slug) {

        $section = StatisticsSection::getSectionFromSlug($this->container, $section_slug);
        if (!$this->get('anketa.access.statistics')->canSeeResults($section->getSeason())) {
            throw new AccessDeniedException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $average = $em->getRepository('AnketaBundle:Answer')
            ->getAverageEvaluationForSubject(
                $section->getSubject(),
                $section->getSeason()
            );

        $response['id'] = $section->getSubject()->getId();
        $response['average'] = $average[1];
        $response['votes'] = $average[2];
        return new Response(json_encode($response));
    }

}
