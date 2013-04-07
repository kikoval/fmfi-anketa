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

use AnketaBundle\AnketaBundle;

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

class StatisticsSubjectSection extends StatisticsSection {

    public function __construct(ContainerInterface $container, Season $season, Subject $subject) {
        $em = $container->get('doctrine.orm.entity_manager');
        $this->setContainer($container);
        $this->season = $season;
        $this->subject = $subject;
        $this->title = $subject->getCode() . ' ' . $subject->getName();
        $this->questionsCategoryType = CategoryType::SUBJECT;
        $this->answersQuery = array('subject' => $subject->getId());
        $this->responsesQuery = array('season' => $season->getId(), 'subject' => $subject->getId(), 'teacher' => null, 'studyProgram' => null);
        $this->activeMenuItems = array($season->getId(), 'subjects', $subject->getCategory(), $subject->getId());
        $this->slug = $season->getSlug() . '/predmet/' . $subject->getSlug();
        $this->associationExamples = 'prednášajúci, cvičiaci, garant predmetu';
    }

    public function getSlug(Season $season = null) {
        if ($season !== null) {
            return $season->getSlug() . '/predmet/' . $this->subject->getSlug();
        }
        return $this->slug;
    }

    /**
     * (non-PHPdoc)
     * @see \AnketaBundle\Controller\StatisticsSection::getPrevSeason()
     */
    public function getPrevSeason() {
        $dql = 'SELECT ss
                   FROM AnketaBundle:SubjectSeason ss JOIN ss.season sn JOIN ss.subject st
                   WHERE sn.ordering < :ordering AND st.id = :subjectid AND ss.studentCountAll IS NOT NULL
                   ORDER BY sn.ordering DESC
                ';
        $em = $this->container->get('doctrine.orm.entity_manager');
        $query = $em->createQuery($dql)->setMaxResults(1);
        $query->setParameter('ordering', $this->season->getOrdering());
        $query->setParameter('subjectid', $this->subject->getId());
        $result = $query->getResult();

        $prevSeason = null;
        if ($result !== null && isset($result[0]))
            $prevSeason = $result[0]->getSeason();

        return $prevSeason;
    }

}

