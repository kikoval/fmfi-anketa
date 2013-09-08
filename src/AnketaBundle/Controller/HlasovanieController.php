<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AnketaBundle\Menu\MenuItemProgressbar;

class HlasovanieController extends AbstractVotingController
{

    public function globalProgressbarAction() {
        $em = $this->get('doctrine.orm.entity_manager');

        $activeSeason = $em->getRepository('AnketaBundle\Entity\Season')->getActiveSeason();
        $total = $activeSeason->getStudentCount();
        $voters = $em->getRepository('AnketaBundle\Entity\User')
                     ->getNumberOfVoters($activeSeason);
        $anon = $em->getRepository('AnketaBundle\Entity\User')
                   ->getNumberOfAnonymizations($activeSeason);

        $templateParams = array();
        $templateParams['progressAnon'] = new MenuItemProgressbar(null, $total, $anon);
        $templateParams['progressVoters'] = new MenuItemProgressbar(null, $total, $voters);
        $templateParams['voters'] = $voters;
        $templateParams['anon'] = $anon;
        return $this->render('AnketaBundle:Hlasovanie:globalProgressbar.html.twig',
                             $templateParams);
    }

}
