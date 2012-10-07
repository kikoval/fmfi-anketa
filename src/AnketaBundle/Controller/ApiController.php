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

        $templateParams['id'] = $section->getSubject()->getId();
        $templateParams['average'] = $average[1];
        $templateParams['votes'] = $average[2];
        return $this->render('AnketaBundle:Api:results.json.twig', $templateParams);
    }

}
