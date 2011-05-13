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

namespace AnketaBundle\Entity\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\EntityRepository;
use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\Category;
use AnketaBundle\Entity\CategoryType;
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
     * Gets the number of questions and filled answers for each category.
     *
     * The result is an associative array of associative arrays, where:
     *
     * - result[categoryId]['questions'] = number of questions in category
     * - result[categoryId]['answer'] = number of answers in category
     * - result[categoryId]['category'] = the top-level category this category
     *   belongs to, i.e. 'general' or 'subject'
     *
     * Note that for 'general' categories, 'answers' is at most 'questions',
     * but for the 'subject' category, 'answers' is at most 'questions' times
     * the number of attended subjects, because each subject has the same
     * questions.
     *
     * @param User $user current user
     * @return array data with the number of questions, answers and top-level category
     */
    public function getGeneralProgress($user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c.id AS cat_id, c.type AS cat_type, COUNT(q.id) AS num
                                   FROM AnketaBundle\Entity\Category c,
                                   AnketaBundle\Entity\Question q
                                   WHERE c.id = q.category
                                   GROUP BY c.id');
        $questionsCount = $query->getResult();

        $query = $em->createQuery('SELECT c.id AS cat_id, c.type AS cat_type, COUNT(a.id) AS num
                                   FROM AnketaBundle\Entity\Category c,
                                        AnketaBundle\Entity\Question q,
                                        AnketaBundle\Entity\Answer a
                                   WHERE c.id = q.category AND q.id = a.question
                                         AND a.author = :authorID
                                         AND (a.option IS NOT NULL OR a.comment IS NOT NULL)
                                   GROUP BY c.id');
        $query->setParameter('authorID', $user->getId());
        $answerCount = $query->getResult();

        /**
         * query resulty maju typ tvaru:
         * [0]
         *      ['cat_id'] => id kategorie
         *      ['cat_type'] => typ kategorie
         *      ['num'] => num count
         * [1]
         *      ['cat_id'] => id kategorie
         *      ['cat_type'] => typ kategorie
         *      ['num']
         * ...
         */
        $result = array();
        foreach ($questionsCount AS $row) {
            $result[$row['cat_id']]['questions'] = $row['num'];
            $result[$row['cat_id']]['category'] = $row['cat_type'];
            // default value for answers
            $result[$row['cat_id']]['answers'] = 0;
        }

        foreach ($answerCount AS $row) {
            $result[$row['cat_id']]['answers'] = $row['num'];
        }

        return $result;
    }

    /**
     *
     * @param User $user current user
     * @return array
     *       result[subjectCode]['answers'] = number of answers for subject
     */
    public function getSubjectProgress($user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT s.code AS subject_code, COUNT(a.id) AS num
                                   FROM AnketaBundle\Entity\Subject s,
                                        AnketaBundle\Entity\Answer a
                                   WHERE s.id = a.subject AND a.author = :authorID
                                         AND (a.option IS NOT NULL OR a.comment IS NOT NULL)
                                   GROUP BY s.id');
        $query->setParameter('authorID', $user->getId());
        $answerSubjectCount = $query->getResult();

        $result = array();
        foreach ($answerSubjectCount AS $row) {
            $result[$row['subject_code']]['answers'] = $row['num'];
        }
        // default values for attended subjects
        foreach ($user->getSubjects() AS $subject) {
            if (!isset($result[$subject->getCode()]))
                    $result[$subject->getCode()]['answers'] = 0;
        }
        return $result;
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
}
