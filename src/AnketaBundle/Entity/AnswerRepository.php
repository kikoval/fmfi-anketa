<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Entity__Repository
 * @author     Jakub Markoš <jakub.markos@gmail.com>
 */

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use AnketaBundle\Entity\Subject;
use AnketaBundle\Entity\User;

/**
 * Repository class for Answer Entity
 */

class AnswerRepository extends EntityRepository {

    /**
     *
     * @param array $questions array of questions
     * @param User $user current user
     * @param Season $season active season
     * @param Subject $subject
     * @return array array of answers, indexed with question ids
     */
    public function getAnswersByCriteria(
            array $questions, User $user, Season $season,
            Subject $subject = null, Teacher $teacher = null,
            StudyProgram $studyProgramme = null)
    {
        // odpoved je jednoznacne identifikovana autorom, id otazky, id predmetu
        // mozno by bolo fajn vytvorit nad tym teda unique index
        $result = array();
        $answerRep = $this->getEntityManager()->getRepository('AnketaBundle\Entity\Answer');
        $criteria = array(
            'author' => $user->getId(),
            'season' => $season->getId()
         );
        if ($subject != null) {
            $criteria['subject'] = $subject->getId();
        }
        if ($teacher != null) {
            $criteria['teacher'] = $teacher->getId();
        }
        if ($studyProgramme != null) {
            $criteria['studyProgram'] = $studyProgramme->getId();
        }
        foreach ($questions AS $question) {
            $criteria['question'] = $question->getId();
            $answer = $answerRep->findOneBy($criteria);
            $result[$question->getId()] = $answer;
        }
        return $result;
    }

    /**
     *
     * @param User $user
     * @param Season $season
     * @return integer number of user answers (not counting answers to subjects
     * not attended)
     */
    public function getAnswersCount($user, $season) {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(a.id)
                                   FROM AnketaBundle\Entity\Answer a
                                   WHERE a.author = :user AND
                                         a.season = :season AND
                                         ((a.subject IS NULL) OR (a.attended = true))');
        $query->setParameter('user', $user);
        $query->setParameter('season', $season);
        return $query->getSingleScalarResult();
    }

    
    public function getAverageEvaluationForTeacher($teacher, $season) {
        $dql = 'SELECT AVG(a.evaluation), COUNT(a.evaluation)  FROM ' .
                'AnketaBundle\Entity\Answer a ' .
                'WHERE ' .
                'a.teacher = :teacher ' .
                'AND a.question = 13 ' .
                'AND a.option is not null ' .
                'AND a.season = :season ';
        $priemer = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('teacher' => $teacher, 'season' => $season));
        return $priemer[0];
    }
    
    public function getAverageEvaluationForSubject($subject, $season) {
        $dql = 'SELECT AVG(a.evaluation), COUNT(a.evaluation)  FROM ' .
                'AnketaBundle\Entity\Answer a ' .
                'WHERE ' .
                'a.subject = :subject ' .
                'AND a.question = 5 ' .
                'AND a.option is not null ' .
                'AND a.season = :season ';
        $priemer = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('subject' => $subject, 'season' => $season));
        return $priemer[0];
    }
}
