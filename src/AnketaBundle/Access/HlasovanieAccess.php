<?php
/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Access
 */

namespace AnketaBundle\Access;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

class HlasovanieAccess
{
    /** @var SecurityContextInterface */
    private $security;

    /** @var EntityManager */
    private $em;

    /** @var string */
    private $allowedOrgUnit;

    /** @var mixed */
    private $user;

    /** @var \AnketaBundle\Entity\UserSeason */
    private $userSeason;

    /** @var \AnketaBundle\Entity\Season */
    private $activeSeason;

    /** @var boolean */
    private $isAdmin;

    public function __construct(SecurityContextInterface $security, EntityManager $em, $allowedOrgUnit) {
        $this->security = $security;
        $this->em = $em;
        $this->allowedOrgUnit = $allowedOrgUnit;
        $this->user = null;
        if ($this->security->getToken() !== null && $this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $token = $this->security->getToken();
            if ($token) $this->user = $token->getUser();
        }
        $this->activeSeason = $this->em->getRepository('AnketaBundle:Season')->getActiveSeason();
        $this->userSeason = null;
        if ($this->user) {
            $this->userSeason = $this->user->forSeason($this->activeSeason);
        }
        $this->isAdmin = $this->security->getToken() !== null && $this->security->isGranted('ROLE_ADMIN');
    }

    /**
     * Returns the logged in user, or null if nobody is logged in.
     *
     * @return mixed
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Returns whether the voting period is currently running (regardless of
     * whether the current user can vote).
     *
     * @return boolean
     */
    public function isVotingOpen() {
        return $this->activeSeason->getVotingOpen();
    }

    /**
     * Returns whether the current user is a student.
     *
     * @return boolean
     */
    public function userIsStudent() {
        return $this->userSeason && $this->userSeason->getIsStudent();
    }

    /**
     * Returns whether the current user belongs to the allowed org. unit.
     *
     * @return boolean
     */
    public function userHasAllowedOrgUnit() {
        return !$this->allowedOrgUnit || ($this->user && in_array($this->allowedOrgUnit, $this->user->getOrgUnits()));
    }

    /**
     * Returns whether the current user can participate in voting.
     *
     * @return boolean
     */
    public function userCanVote() {
        if ($this->isAdmin) return true;
        return $this->isVotingOpen() && $this->userSeason && $this->userSeason->canVote() && $this->userHasAllowedOrgUnit();
    }

}
