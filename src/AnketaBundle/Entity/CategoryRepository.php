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
    public function getOrderedGeneral() {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT c
                                   FROM AnketaBundle\Entity\Category c
                                   WHERE c.type = :type
                                   ORDER BY c.position ASC");
        $query->setParameter('type', CategoryType::GENERAL);
        return $query->getResult();
    }
}
