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
use AnketaBundle\Entity\TeachersSubjects;

/**
 * Repository class for TeacherSubjectEntity
 */

class TeachersSubjectsRepository extends EntityRepository {

    public function teachesByLogin($login, $subject, $season) {
        $dql = 'SELECT COUNT(ts) ';
        $dql .= ' FROM AnketaBundle\Entity\TeachersSubjects ts, AnketaBundle\Entity\Teacher t ';
        $dql .= ' WHERE ts.season = :season AND ts.subject = :subject AND ts.teacher = t.id AND t.login = :login';
        $result = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('login' => $login,
                            'subject' => $subject, 'season' => $season));
        return $result[0] == 1;
    }

}