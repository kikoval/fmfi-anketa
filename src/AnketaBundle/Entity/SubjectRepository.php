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
    /**
     * Compares 2 subjects based on their name property
     *
     * @deprecated TODO: remove this function and sort in database!
     */
    public static function compareSubjects($a, $b) {
        if ($a == $b) {
            return 0;
        }
        return \strcmp($a->getName(), $b->getName());
    }

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

    /**
     * @todo season as parameter
     */
    public function getSortedSubjects() {
        $subjects = $this->getEntityManager()
                         ->getRepository('AnketaBundle\Entity\Subject')
                         ->findAll();
        \usort($subjects, array('\AnketaBundle\Entity\SubjectRepository', 'compareSubjects'));
        return $subjects;
    }

    /**
     * @todo season as parameter
     */
    public function getSortedSubjectsWithAnswers() {
        // Note: JOIN does not work here, see
        // http://www.doctrine-project.org/jira/browse/DDC-1001
        $dql = 'SELECT DISTINCT s FROM AnketaBundle\Entity\Answer a, ' .
            'AnketaBundle\Entity\Subject s ' . 
            'WHERE a.subject = s';
        $subjects = $this->getEntityManager()
                         ->createQuery($dql)->execute();
        \usort($subjects, array('\AnketaBundle\Entity\SubjectRepository', 'compareSubjects'));
        return $subjects;
    }

}
