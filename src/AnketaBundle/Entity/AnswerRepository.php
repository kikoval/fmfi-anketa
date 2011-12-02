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
use AnketaBundle\Entity\Teacher;
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
     * @param Subject $subject
     * @return array array of answers, indexed with question ids
     */
    public function getAnswersByCriteria(
            array $questions, User $user,
            Subject $subject = null, Teacher $teacher = null,
            StudyProgram $studyProgramme = null)
    {
        // odpoved je jednoznacne identifikovana autorom, id otazky, id predmetu
        // mozno by bolo fajn vytvorit nad tym teda unique index
        $result = array();
        $answerRep = $this->getEntityManager()->getRepository('AnketaBundle\Entity\Answer');
        $criteria = array('author' => $user->getId());
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
     * @return integer number of user answers (not counting answers to subjects
     * not attended)
     */
    public function getAnswersCount($user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(a.id)
                                   FROM AnketaBundle\Entity\Answer a
                                   WHERE a.author = :userId AND
                                         ((a.subject IS NULL) OR (a.attended = true))');
        $query->setParameter('userId', $user->getId());
        return $query->getSingleScalarResult();
    }
}
