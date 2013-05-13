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

use Doctrine\ORM\EntityRepository;
use AnketaBundle\Entity\CategoryType;

class CategoryRepository extends EntityRepository {

    /**
     *
     * @return ArrayCollection general categories ordered by position
     */
    public function getOrderedGeneral(Season $season = null) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('c')
           ->from('AnketaBundle:Category', 'c')
           ->where($qb->expr()->eq('c.type', ':type'));
        if ($season !== null) {
            $qb->andWhere($qb->expr()->exists(
                    'SELECT q 
                     FROM AnketaBundle:Question q
                     WHERE q.season = :season
                     AND q.category = c'));
        }
        $qb->orderBy('c.position', 'ASC');
        $query = $qb->getQuery();
        $query->setParameter('type', CategoryType::GENERAL);
        if ($season !== null) {
            $query->setParameter('season', $season);
        }
        return $query->getResult();
    }
}
