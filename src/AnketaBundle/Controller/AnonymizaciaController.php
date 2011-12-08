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

            $this->get('session')->setFlash('anonymizacia', 'Vaše hlasovanie v ankete bolo úspešne ukončené.');

            // Remove the token, but keep the session
            // as the user is not changing and we want
            // to keep the flash. We may do this as
            // we use cosign provider that will automatically
            // authenticate the user upon next request
            $securityContext = $this->get('security.context');
            $securityContext->setToken(null);

            // TODO(anty): the following does not work as
            // there is some problem with our user provider
            // see Issue 12
            //$securityContext->getToken()->setAuthenticated(false);

            return new RedirectResponse($this->generateUrl('anketa'));
        }
        
        return $this->render('AnketaBundle:Anonymizacia:anonymizuj.html.twig');
    }

}
