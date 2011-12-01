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

/**
 * Repository class for Teacher Entity
 */

class TeacherRepository extends EntityRepository {
    
    public function getTeachersForSubject($subject, $season) {
        $dql = 'SELECT t FROM AnketaBundle\Entity\Teacher t, ' .
                  'AnketaBundle\Entity\TeachersSubjects ts WHERE t = ts.teacher ' .
                  ' AND ts.subject = :subject ' .
                  ' AND ts.season = :season ' .
                  ' ORDER BY t.familyName, t.givenName';

        $teachers = $this->getEntityManager()
                         ->createQuery($dql)->execute(array('subject' => $subject,
                             'season' => $season));
        return $teachers;
    }
    
}