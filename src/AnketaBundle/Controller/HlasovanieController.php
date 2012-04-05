<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AnketaBundle\Menu\MenuItemProgressbar;
use AnketaBundle\Entity\User;

class HlasovanieController extends Controller
{
    public function indexAction()
    {
        // TODO: toto chceme aby rovno redirectovalo na prvu ne-100% sekciu
        $security = $this->get('security.context');
        if ($security->isGranted('ROLE_HAS_VOTE')) {
            return new RedirectResponse($this->generateUrl('answer_incomplete'));
        }
        else if ($security->isGranted('ROLE_STUDENT')) {
            return $this->render('AnketaBundle:Hlasovanie:dakujeme.html.twig');
        }
        else {
            return $this->render('AnketaBundle:Hlasovanie:novote.html.twig');
        }
    }

    public function globalProgressbarAction($mode) {
        $em = $this->get('doctrine.orm.entity_manager');

        $total = $em->getRepository('AnketaBundle\Entity\Season')
                    ->getActiveSeason()
                    ->getStudentCount();
        $voters = $em->getRepository('AnketaBundle\Entity\User')
                     ->getNumberOfVoters();
        $anon = $em->getRepository('AnketaBundle\Entity\User')
                   ->getNumberOfAnonymizations();

        $templateParams = array();
        $templateParams['progressAnon'] = new MenuItemProgressbar(null, $total, $anon);
        $templateParams['progressVoters'] = new MenuItemProgressbar(null, $total, $voters);
        $templateParams['voters'] = $voters;
        $templateParams['anon'] = $anon;
        $templateParams['mode'] = $mode;
        return $this->render('AnketaBundle:Hlasovanie:globalProgressbar.html.twig',
                             $templateParams);
    }

}
