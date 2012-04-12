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
        // TODO(anty): Toto je docasne riesenie, kedze som vyhodil datumy
        // zo season (datumy sa budu riesit vo fazach)
        // MIN je z toho dovodu, ze ked niekto nahodou vlozi do season
        // dalsi zaznam, aktivna zostane rovnaka season,
        // ked budeme chciet oficialne podporovat viac season
        // v tabulke, tak bud pridame slpec active alebo nieco take
        $dql = 'SELECT s FROM AnketaBundle\Entity\Season s ' .
               'WHERE s.id = ' .
                    '(SELECT MIN(s2.id) ' .
                    ' FROM AnketaBundle\Entity\Season s2)';
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

    public function getTopLevelResultsVisible() {
        $dql = 'SELECT s FROM AnketaBundle\Entity\Season s ' .
               'WHERE s.resultsVisible = TRUE';
        $query = $this->getEntityManager()->createQuery($dql);
        $result = $query->execute();
        return count($result) > 0;
    }

}
