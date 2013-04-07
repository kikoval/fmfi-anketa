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

class StatisticsGeneralSection extends StatisticsSection {

    public function __construct(ContainerInterface $container, Season $season, Question $generalQuestion) {
        if ($generalQuestion->getCategory()->getType() != CategoryType::GENERAL) {
            throw new NotFoundHttpException('Section not found: Question is not general.');
        }
        $this->setContainer($container);
        $this->season = $season;
        $this->generalQuestion = $generalQuestion;
        $this->title = $generalQuestion->getQuestion();
        $this->headingVisible = false;
        $this->answersQuery = array();
        $this->responsesQuery = array('season' => $season->getId(), 'question' => $generalQuestion->getId());
        $this->activeMenuItems = array($season->getId(), 'general');
        $this->slug = $season->getSlug() . '/vseobecne/' . $generalQuestion->getId();
        $this->associationExamples = 'vedenie fakulty, vedúci katedry, vyučujúci';
    }
    
    public function getSlug(Season $season = null) {
        if ($season !== null) {
            return $season->getSlug() . '/vseobecne/' . $this->generalQuestion->getId();
        }
        return $this->slug;
    }
    
    /**
     * (non-PHPdoc)
     * @see \AnketaBundle\Controller\StatisticsSection::getPrevSeason()
     */
    // TODO relation general-season is missing, so assuming general are there for all seasons
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

