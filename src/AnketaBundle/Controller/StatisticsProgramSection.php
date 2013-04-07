<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Controller
 */

namespace AnketaBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use AnketaBundle\Entity\Season;
use AnketaBundle\Entity\Subject;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\StudyProgram;
use AnketaBundle\Entity\Answer;
use AnketaBundle\Entity\Response;
use AnketaBundle\Entity\CategoryType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StatisticsProgramSection extends StatisticsSection {

    public function __construct(ContainerInterface $container, Season $season, StudyProgram $studyProgram) {
        $this->setContainer($container);
        $this->season = $season;
        $this->studyProgram = $studyProgram;
        $this->title = $studyProgram->getCode() . ' ' . $studyProgram->getName();
        $this->questionsCategoryType = CategoryType::STUDY_PROGRAMME;
        $this->answersQuery = array('studyProgram' => $studyProgram->getId());
        $this->responsesQuery = array('season' => $season->getId(), 'studyProgram' => $studyProgram->getId(), 'teacher' => null, 'subject' => null);
        $this->activeMenuItems = array($season->getId(), 'study_programs', $studyProgram->getCode());
        $this->slug = $season->getSlug() . '/program/' . $studyProgram->getSlug();
        $this->associationExamples = 'garant, tútor, vedúci katedry, vyučujúci niektorého predmetu';
    }

    public function getSlug(Season $season = null) {
        if ($season !== null) {
            return $season->getSlug() . '/program/' . $this->studyProgram->getSlug();
        }
        return $this->slug;
    }
    
    /**
     * (non-PHPdoc)
     * @see \AnketaBundle\Controller\StatisticsSection::getPrevSeason()
     */
    // TODO relation programme-season is missing, so assuming programmes are there for all seasons
    public function getPrevSeason() {
        $dql = 'SELECT sn
                   FROM AnketaBundle:Season sn
                   WHERE sn.ordering < :ordering
                   ORDER BY sn.ordering DESC
                ';
        $em = $this->container->get('doctrine.orm.entity_manager');
        $query = $em->createQuery($dql)->setMaxResults(1);
        $query->setParameter('ordering', $this->season->getOrdering());
        $prevSeason = $query->getOneOrNullResult();

        return $prevSeason;
    }
}
