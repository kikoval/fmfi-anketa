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

/**
 * Repository class for Option Entity
 */

class OptionRepository extends EntityRepository {
    
    public function getOptions($limit = 5) {
        $dql = 'SELECT o, q FROM AnketaBundle\Entity\Option o ' .
               'INNER JOIN o.question q';

        $query = $this->getEntityManager()->createQuery($dql);
        
        return $query->getResult();
    }
}