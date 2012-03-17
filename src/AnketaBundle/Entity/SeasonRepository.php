<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Entity__Repository
 * @author     Peter Peresini <ppershing@gmail.com>
 */


namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class SeasonRepository extends EntityRepository {

    public function getActiveSeason() {

        $dql = 'SELECT s FROM AnketaBundle\Entity\Season s ' .
               'WHERE s.active = TRUE';
        $query = $this->getEntityManager()->createQuery($dql);
        $result = $query->execute();

        if (count($result) > 1) {
            throw new NonUniqueResultException();
        }
        if (count($result) == 0) {
            throw new NoResultException();
        }
        return array_shift($result);
    }

}
