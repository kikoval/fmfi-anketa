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

class StatisticsTeacherSubjectSection extends StatisticsSection {

    public function __construct(ContainerInterface $container, Season $season, Subject $subject, User $teacher) {
        $em = $container->get('doctrine.orm.entity_manager');
        if ($em->getRepository('AnketaBundle:TeachersSubjects')->findOneBy(array('teacher' => $teacher->getId(), 'subject' => $subject->getId(), 'season' => $season->getId())) === null) {
            throw new NotFoundHttpException('Section not found: Teacher "'.$teacher->getId().'" doesn\'t teach subject "'.$subject->getId().'".');
        }
        $this->setContainer($container);
        $this->season = $season;
        $this->subject = $subject;
        $this->teacher = $teacher;
        $this->title = $subject->getCode() . ' ' . $subject->getName() . ' - ' . $teacher->getFormattedName();
        $this->questionsCategoryType = CategoryType::TEACHER_SUBJECT;
        $this->answersQuery = array('subject' => $subject->getId(), 'teacher' => $teacher->getId());
        $this->responsesQuery = array('season' => $season->getId(), 'subject' => $subject->getId(), 'teacher' => $teacher->getId(), 'studyProgram' => null);
        $this->activeMenuItems = array($season->getId(), 'subjects', $subject->getCategory(), $subject->getId(), $teacher->getId());
        $this->slug = $season->getSlug() . '/predmet/' . $subject->getSlug() . '/ucitel/' . $teacher->getId();
        $this->associationExamples = 'prednášajúci, cvičiaci, garant predmetu';
    }

    public function getSlug(Season $season = null) {
        if ($season !== null) {
            return $season->getSlug() . '/predmet/' . $this->subject->getSlug() . '/ucitel/' . $this->teacher->getId();
        }
        return $this->slug;
    }
    
    /**
     * (non-PHPdoc)
     * @see \AnketaBundle\Controller\StatisticsSection::getPrevSeason()
     */
 	public function getPrevSeason() {
		$dql = 'SELECT ss
       			FROM AnketaBundle:SubjectSeason ss JOIN ss.season sn JOIN ss.subject st JOIN AnketaBundle:TeachersSubjects ts
       			WHERE sn.ordering < :ordering AND st.id = :subjectid AND ss.studentCountAll IS NOT NULL AND ts.id = :teacherid
       			ORDER BY sn.ordering DESC
				';
		$em = $this->container->get('doctrine.orm.entity_manager');
    	$query = $em->createQuery($dql)->setMaxResults(1);
    	$query->setParameter('ordering', $this->season->getOrdering());
    	$query->setParameter('subjectid', $this->subject->getId());
    	$query->setParameter('teacherid', $this->teacher->getId());
    	$result = $query->getResult();
    	
    	$prevSeason = null;
    	if ($result !== null && isset($result[0]))
    		$prevSeason = $result[0]->getSeason();
    	
    	return $prevSeason;
    }
}
