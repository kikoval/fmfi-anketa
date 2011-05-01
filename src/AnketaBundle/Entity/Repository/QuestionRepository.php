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

use Doctrine\ORM\EntityRepository;
use AnketaBundle\Entity\Question;

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
     * @param User $user
     * @return integer number of questions accessible by user
     */
    public function getQuestionsCount($user) {
        $em = $this->getEntityManager();
        $category = $em->getRepository('AnketaBundle\Entity\Category')
                       ->findOneBy(array('category' => 'subject'));
        $query = $em->createQuery('SELECT COUNT(q.id)
                                   FROM AnketaBundle\Entity\Question q');
        $result = $query->getSingleScalarResult();
        $query = $em->createQuery('SELECT COUNT(q.id)
                                   FROM AnketaBundle\Entity\Question q
                                   WHERE q.category = :subjectCatId');
        $query->setParameter('subjectCatId', $category->getId());
        $subCount = $query->getSingleScalarResult();
        $result += $subCount * ($user->getSubjectsCount() - 1);
        return $result;
    }
}