<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AnonymizaciaController extends Controller {

    public function preExecute() {
        if (!$this->get('anketa.access.hlasovanie')->userCanVote()) throw new AccessDeniedException();
    }

    public function anonymizujAction() {
        $request = $this->get('request');
        if ($request->getMethod() == 'POST' && $request->request->get('anonymizuj')) {
            $em = $this->get('doctrine.orm.entity_manager');
            $user = $this->get('security.context')->getToken()->getUser();
            $season = $em->getRepository('AnketaBundle:Season')->getActiveSeason();
            $userSeason = $em->getRepository('AnketaBundle:UserSeason')->findOneBy(array('user' => $user->getId(), 'season' => $season->getId()));
            $userSeason->setFinished(true);
	    $userSeason->setFinishTimestamp(new \DateTime('now'));
            $em->persist($user);
            $em->getRepository('AnketaBundle\Entity\User')
               ->anonymizeAnswersByUser($user, $season);
            $em->flush();

            $message = $this->get('translator')->trans('anonymizacia.controller.uspesny_koniec');
            $this->get('session')->getFlashBag()->add('anonymizacia', $message);

            return new RedirectResponse($this->generateUrl('anketa'));
        }

        return $this->render('AnketaBundle:Anonymizacia:anonymizuj.html.twig');
    }

}
