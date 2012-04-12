<?php

namespace AnketaBundle\Access;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

class HlasovanieAccess
{
    private $security;

    private $em;

    private $allowedOrgUnit;

    private $user = null;

    private $userSeason = null;

    private $activeSeason;

    private $isAdmin;

    public function __construct(SecurityContextInterface $security, EntityManager $em, $allowedOrgUnit) {
        $this->security = $security;
        $this->em = $em;
        $this->allowedOrgUnit = $allowedOrgUnit;
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $token = $this->security->getToken();
            if ($token) $this->user = $token->getUser();
        }
        $this->activeSeason = $this->em->getRepository('AnketaBundle:Season')->getActiveSeason();
        if ($this->user) {
            $this->userSeason = $this->user->forSeason($this->activeSeason);
        }
        $this->isAdmin = $this->security->isGranted('ROLE_ADMIN');
    }

    public function getUser() {
        return $this->user;
    }

    public function getUserSeason() {
        return $this->userSeason;
    }

    public function isVotingOpen() {
        return $this->activeSeason->getVotingOpen();
    }

    public function userIsStudent() {
        return $this->userSeason && $this->userSeason->getIsStudent();
    }

    public function userHasAllowedOrgUnit() {
        return !$this->allowedOrgUnit || ($this->user && in_array($this->allowedOrgUnit, $this->user->getOrgUnits()));
    }

    public function userCanVote() {
        if ($this->isAdmin) return true;
        return $this->isVotingOpen() && $this->userSeason && $this->userSeason->canVote() && $this->userHasAllowedOrgUnit();
    }

}
