<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AnonymizaciaController extends Controller {
    
    public function anonymizujAction() {
        $request = $this->get('request');
        if ($request->getMethod() == 'POST' && $request->request->get('anonymizuj')) {
            $em = $this->get('doctrine.orm.entity_manager');
            $user = $this->get('security.context')->getToken()->getUser();
            $user->setHasVote(false);
            $em->persist($user);
            $em->getRepository('AnketaBundle\Entity\User')
               ->anonymizeAnswersByUserId($user->getId());
            $em->flush();

            $this->get('session')->setFlash('anonymizacia', 'Vaše hlasovanie v Ankete 2011 bolo úspešne ukončené.');
            return new RedirectResponse($this->generateUrl('dakujeme'));
        }
        
        return $this->render('AnketaBundle:Anonymizacia:anonymizuj.html.twig');
    }

    public function dakujemeAction() {
        $user = $this->get('security.context')->getToken()->getUser();
        if ($user->getHasVote()) return new RedirectResponse($this->generateUrl('answer_incomplete'));
        
        return $this->render('AnketaBundle:Anonymizacia:dakujeme.html.twig');
    }

}
