<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Entity__Repository
 * @author     Jakub Markoš <jakub.markos@gmail.com>
 * @author     Martin Králik <majak47@gmail.com>
 */

namespace AnketaBundle\Entity\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\EntityRepository;
use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\Category;
use AnketaBundle\Entity\CategoryType;
use AnketaBundle\Entity\User;
use fajr\libfajr\base\Preconditions;
/**
 * Repository class for Question Entity
 */

class QuestionRepository extends EntityRepository {
    
    public function getQuestion($id) {
        $dql = 'SELECT q, o FROM AnketaBundle\Entity\Question q '.
               'INNER JOIN q.options o WHERE q.id = :id';
        
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('id', $id);
        
        return $query->getSingleResult();
    }

    /**
     *
     * @param Category $category category of the questions to look for
     * @return ArrayCollection questions ordered by position
     */
    public function getOrderedQuestions(Category $category) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT q, o
                                   FROM AnketaBundle\Entity\Question q
                                   LEFT JOIN q.options o
                                   WHERE q.category = :category
                                   ORDER BY q.position ASC");
        $query->setParameter('category', $category->getId());
        return $query->getResult();
    }

    //TODO(ppershing): Toto je cele zle.
    //                 Nefunguje to pre "general" kategoriu,
    //                 kde to vrati pocet otazok iba prvej kategorie.
    //                 Navrhujem tuto funkciu zrusit a pouzivat iba tu nad nou.
    public function getOrderedQuestionsByCategoryType($type) {
        Preconditions::check(CategoryType::isValid($type));
        $category = $this->getEntityManager()
                ->getRepository('AnketaBundle\Entity\Category')
                ->findOneBy(array('type' => $type));
        if ($category == null) {
            throw new NoResultException();
        }
        return $this->getOrderedQuestions($category);
    }

     public function getNumberOfQuestionsForCategoryType($type) {
        Preconditions::check(CategoryType::isValid($type));
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT COUNT(q.id) as questions
                                   FROM AnketaBundle\Entity\Question q
                                   JOIN q.category c
                                   WHERE c.type = :type");
        $result = $query->setParameter('type', $type)->getResult();
        return $result[0]['questions'];
    }

    /**
     * Returned array contains progress for provided user in following format:
     * - result[subject_code]['answered'] = number of answers for subject
     * - result[subject_code]['total'] = number of questions for subject
     * Includes progress for teacher with the largest progress!
     * @param User $user
     * @return array
     */
    public function getProgressForSubjectsByUser(User $user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT s.code AS subject_code, COUNT(a.id) AS num
                                   FROM AnketaBundle\Entity\Answer a
                                   JOIN a.subject s
                                   WHERE a.author = :authorID
                                         AND (a.option IS NOT NULL OR a.comment IS NOT NULL)
                                         AND a.teacher IS NULL
                                   GROUP BY s.id');
        $rows = $query->setParameter('authorID', $user->getId())->getResult();

        $subjectQuestions = $this->getNumberOfQuestionsForCategoryType(CategoryType::SUBJECT);
        $subjectTeachersQuestions = $this->getNumberOfQuestionsForCategoryType(CategoryType::TEACHER_SUBJECT);

        $teachers = $this->getProgressForSubjectTeachersByUser($user);

        $mostCompleteTeacher = function(array $array)
        {
            return \max(\array_map(function($value){return $value['answered'];}, $array));
        };

        $result = array();
        foreach ($user->getSubjects() as $subject) {
                $result[$subject->getCode()] = array(
                    'answered' => 0,
                    'total' => $subjectQuestions
                );
        }
        foreach ($rows as $row) {
            $result[$row['subject_code']]['answered'] = $row['num'];
            if (array_key_exists($row['subject_code'], $teachers)) {
                $result[$row['subject_code']]['answered'] += $mostCompleteTeacher($teachers[$row['subject_code']]);
                $result[$row['subject_code']]['total'] += $subjectTeachersQuestions;
            }
        }
        
        return $result;
    }

    /**
     * Returned array contains progress for provided user in following format:
     * - result[subject_code][teacher_id]['answered'] = number of answers for subject and teacher
     * - result[subject_code][teacher_id]['total'] = number of questions for subject and teacher
     * @param User $user
     * @return array
     */
    public function getProgressForSubjectTeachersByUser(User $user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT s.code AS subject_code, t.id AS teacher_id, COUNT(a.id) AS num
                                   FROM AnketaBundle\Entity\Answer a
                                        JOIN a.subject s
                                        JOIN a.teacher t
                                   WHERE a.author = :authorID
                                         AND (a.option IS NOT NULL OR a.comment IS NOT NULL)
                                   GROUP BY s.id, a.teacher');
        $rows = $query->setParameter('authorID', $user->getId())->getResult();

        $subjectTeachersQuestions = $this->getNumberOfQuestionsForCategoryType(CategoryType::TEACHER_SUBJECT);

        $result = array();
        foreach ($user->getSubjects() as $subject) {
            foreach ($subject->getTeachers() as $teacher) {
                $result[$subject->getCode()][$teacher->getId()] = array(
                    'answered' => 0,
                    'total' => $subjectTeachersQuestions
                );
            }
        }

        foreach ($rows as $row) {
            $result[$row['subject_code']][$row['teacher_id']]['answered'] = $row['num'];
        }

        return $result;
    }

    /**
     * Returned array contains progress for provided user in following format:
     * - result[cat_id]['answered'] = number of answers for category
     * - result[cat_id]['total'] = number of questions for category
     * @param User $user
     * @return array
     */
    public function getProgressForCategoriesByUser(User $user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c.id AS cat_id, COUNT(a.id) AS num
                                   FROM AnketaBundle\Entity\Answer a
                                        JOIN a.question q
                                        JOIN q.category c
                                   WHERE a.author = :authorID
                                         AND (a.option IS NOT NULL OR a.comment IS NOT NULL)
                                   GROUP BY c.id');
        $rows = $query->setParameter('authorID', $user->getId())->getResult();

        $result = array();
        foreach ($em->getRepository('AnketaBundle\Entity\Category')->findAll() as $category) {
                $result[$category->getId()] = array(
                    'answered' => 0,
                    'total' => $category->getQuestionsCount()
                );
        }

        foreach ($rows as $row) {
            $result[$row['cat_id']]['answered'] = $row['num'];
        }

        return $result;
    }

    // number of users who provided at least one answer (even if they deleted it afterwards)
    public function getNumberOfVoters() {
        $em = $this->getEntityManager();
        $result = $em->createQuery("SELECT COUNT(DISTINCT a.author) AS num
                                    FROM AnketaBundle\Entity\Answer a")
                     ->getResult();
        return $result[0]['num'];
    }
}
