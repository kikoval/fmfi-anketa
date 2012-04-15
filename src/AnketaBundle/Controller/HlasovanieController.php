<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AnketaBundle\Menu\MenuItemProgressbar;
use AnketaBundle\Entity\User;

class HlasovanieController extends Controller
{
    public function indexAction()
    {
        $access = $this->get('anketa.access.hlasovanie');
        if (!$access->isVotingOpen()) throw new AccessDeniedException();

        if (!$access->userIsStudent()) {
            return $this->render('AnketaBundle:Hlasovanie:novote.html.twig');
        }

        if (!$access->userHasAllowedOrgUnit()) {
            $user = $access->getUser();
            $allowedOrgUnit = $this->container->getParameter('org_unit');
            $params = array();
            $params['org_unit'] = $allowedOrgUnit;
            $otherInstances = $this->container->getParameter('other_instances');
            $recommendedInstances = array();
            foreach ($otherInstances as $key => $definition) {
                if (in_array($key, $user->getOrgUnits())) {
                    $recommendedInstances[] = $definition;
                }
            }
            $params['recommended_instances'] = $recommendedInstances;
            return $this->render('AnketaBundle:Hlasovanie:orgunit.html.twig',
                    $params);
        }

        if ($access->userCanVote()) {
            // TODO: toto chceme aby rovno redirectovalo na prvu ne-100% sekciu
            return new RedirectResponse($this->generateUrl('answer_incomplete'));
        }

        return $this->render('AnketaBundle:Hlasovanie:dakujeme.html.twig');
    }

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
