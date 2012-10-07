<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiController extends Controller {

    public function requestFailed($reason) {
        return $this->render(
            'AnketaBundle:Api:error.json.twig',
            array('reason' => $reason)
        );
    }

    public function resultsAction($section_slug) {

        try {
            $section = StatisticsSection::getSectionFromSlug($this->container, $section_slug);
        } catch (NotFoundHttpException $e) {
            return $this->requestFailed($e->getMessage());
        }
        if (!$this->get('anketa.access.statistics')->canSeeResults($section->getSeason())) {
            return $this->requestFailed('Requested result is not available.');
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
