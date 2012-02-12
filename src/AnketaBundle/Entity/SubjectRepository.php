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
 * Repository class for Subject Entity
 */
class SubjectRepository extends EntityRepository {

    public function getAttendedSubjectsForUser($user, $season) {
        $dql = 'SELECT s FROM AnketaBundle\Entity\Subject s, ' .
                'AnketaBundle\Entity\UsersSubjects us WHERE s = us.subject ' .
                ' AND us.user = :user ' .
                ' AND us.season = :season ' .
                ' ORDER BY s.name';

        $subjects = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('user' => $user,
            'season' => $season));
        return $subjects;
    }
    
    public function getSubjectsForTeacher($teacher, $season) {
        $dql = 'SELECT s FROM AnketaBundle\Entity\Subject s, ' .
                'AnketaBundle\Entity\TeachersSubjects ts WHERE s = ts.subject ' .
                ' AND ts.teacher = :teacher ' .
                ' AND ts.season = :season ' .
                ' ORDER BY s.name';

        $subjects = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('teacher' => $teacher,
            'season' => $season));
        return $subjects;
    }

    public function getSortedSubjectsWithAnswers($season) {
        $dql = 'SELECT DISTINCT s FROM AnketaBundle\Entity\Answer a, ' .
                'AnketaBundle\Entity\Subject s ' .
                'WHERE a.subject = s ' .
                'AND a.season = :season ' .
                'ORDER BY s.name';
        $subjects = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('season' => $season));
        return $subjects;
    }

}
