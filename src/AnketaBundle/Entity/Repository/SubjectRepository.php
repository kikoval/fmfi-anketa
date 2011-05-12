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
 * Repository class for Subject Entity
 */

class SubjectRepository extends EntityRepository {
    /**
     * Compares 2 subjects based on their name property
     */
    public static function compareSubjects($a, $b) {
        if ($a == $b) {
            return 0;
        }
        return \strcmp($a->getName(), $b->getName());
    }

    public function getAttendedSubjectForUser($userId) {
        $user = $this->getEntityManager()->find('AnketaBundle\Entity\User',
                                                $userId);
        $attendedSubjects = $user->getSubjects()->toArray();
        \usort($attendedSubjects, array('\AnketaBundle\Entity\Repository\SubjectRepository', 'compareSubjects'));
        return $attendedSubjects;
    }

}