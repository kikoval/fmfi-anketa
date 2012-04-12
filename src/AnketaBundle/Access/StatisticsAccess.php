<?php

namespace AnketaBundle\Access;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use AnketaBundle\Entity\Response;

class StatisticsAccess
{
    private $security;

    private $em;

    private $user = null;

    private $isAdmin;

    public function __construct(SecurityContextInterface $security, EntityManager $em) {
        $this->security = $security;
        $this->em = $em;
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $token = $this->security->getToken();
            if ($token) $this->user = $token->getUser();
        }
        $this->isAdmin = $this->security->isGranted('ROLE_ADMIN');
    }

    public function getUser() {
        return $this->user;
    }

    public function hasOwnResponses() {
        return $this->security->isGranted('ROLE_TEACHER');
    }

    public function hasOwnSubjects() {
        return $this->security->isGranted('ROLE_TEACHER');
    }

    public function hasFullResults() {
        return $this->security->isGranted('ROLE_FULL_RESULTS');
    }

    public function canSeeTopLevelResults() {
        return $this->em->getRepository('AnketaBundle:Season')->getTopLevelResultsVisible();
    }

    public function activeSeasonHasVisibleResults() {
        $activeSeason = $this->em->getRepository('AnketaBundle:Season')->getActiveSeason();
        return $activeSeason->getResultsVisible();
    }

    public function canSeeResults($season) {
        if ($this->isAdmin) return true;
        return $season->getResultsVisible() && ($season->getResultsPublic() || (bool)$this->user);
    }

    public function commentsBlocked($season) {
        return !$this->user;
    }

    public function canCreateResponses($season) {
        return $this->canSeeResults($season) && $this->hasOwnResponses() && $season->getRespondingOpen();
    }

    public function canEditResponse(Response $response) {
        if (!$this->canSeeResults($response->getSeason())) return false;
        return $this->user && $this->user->getUserName() === $response->getAuthorLogin() && $response->getSeason()->getRespondingOpen();
    }

    public function canSeeResponses($season) {
        return $this->canSeeResults($season) && ($season->getResponsesVisible() || $this->canCreateResponses($season));
    }

    public function hasStudyProgrammeReports() {
        return $this->security->isGranted('ROLE_STUDY_PROGRAMME_REPORT') || $this->hasAllReports();
    }

    public function hasDepartmentReports() {
        return $this->security->isGranted('ROLE_DEPARTMENT_REPORT') || $this->hasAllReports();
    }

    public function hasAllReports() {
        return $this->security->isGranted('ROLE_ALL_REPORTS');
    }

    public function hasReports() {
        return $this->hasStudyProgrammeReports() || $this->hasDepartmentReports();
    }

}
