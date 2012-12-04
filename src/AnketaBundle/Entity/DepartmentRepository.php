<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Entity__Repository
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */

namespace AnketaBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DepartmentRepository extends EntityRepository {

    public function findByUser($user, $season) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT d
                           FROM AnketaBundle\\Entity\\Department d,
                           AnketaBundle\\Entity\\UserSeason us
                           WHERE us.user = :user
                           AND us.season = :season
                           AND us.department = d");
        $query->setParameter('user', $user);
        $query->setParameter('season', $season);
        
        $depts = $query->getResult();
        
        return $depts;
    }
}