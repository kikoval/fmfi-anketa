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
use AnketaBundle\Entity\StudyProgram;

/**
 * Repository class for Program Entity
 */

class StudyProgramRepository extends EntityRepository {

    public function getStudyProgrammesForUser($user, $season) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT sp
                           FROM AnketaBundle\\Entity\\UsersSubjects us,
                           AnketaBundle\\Entity\\StudyProgram sp
                           WHERE us.user = :user
                           AND us.studyProgram = sp
                           AND us.season = :season
                           ORDER BY sp.code ASC");
        $query->setParameter('user', $user);
        $query->setParameter('season', $season);

        return $query->getResult();
    }

    public function getFirstStudyProgrammeForUser($user, $season) {
        $result = $this->getStudyProgrammesForUser($user, $season);
        if (!empty($result[0])) return $result[0];
        else return null;
    }

    public function getAllWithAnswers($season) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT sp
                           FROM AnketaBundle\\Entity\\StudyProgram sp,
                           AnketaBundle\\Entity\\Answer a
                           WHERE sp.id = a.studyProgram
                           AND a.teacher IS NULL
                           AND a.subject IS NULL
                           AND a.season = :season
                           GROUP BY sp.id
                           HAVING COUNT(a) > 0
                           ORDER BY sp.code ASC");
        $query->setParameter('season', $season);
        return $query->getResult();
    }

}