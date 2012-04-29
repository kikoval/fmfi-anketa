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
        $dql = 'SELECT t FROM AnketaBundle\Entity\User t, ' .
                  'AnketaBundle\Entity\TeachersSubjects ts WHERE t = ts.teacher ' .
                  ' AND ts.subject = :subject ' .
                  ' AND ts.season = :season ' .
                  ' ORDER BY t.familyName, t.givenName';

        $teachers = $this->getEntityManager()
                         ->createQuery($dql)->execute(array('subject' => $subject,
                             'season' => $season));
        return $teachers;
    }
    
    public function getTeachersForSubjectWithAnswers($subject, $season) {
        $dql = 'SELECT t FROM AnketaBundle\Entity\User t, ' .
                  'AnketaBundle\Entity\TeachersSubjects ts, ' .
                  'AnketaBundle\Entity\Answer a ' .
                  'WHERE t = ts.teacher ' .
                  ' AND t.displayName IS NOT NULL ' .
                  ' AND ts.subject = :subject ' .
                  ' AND ts.season = :season ' .
                  ' AND a.subject = :subject ' .
                  ' AND a.teacher = t ' .
                  ' AND a.season = :season ' .
                  ' AND a.option is not null ' .
                  ' ORDER BY t.familyName, t.givenName';

        $teachers = $this->getEntityManager()
                         ->createQuery($dql)->execute(array('subject' => $subject,
                             'season' => $season));
        return $teachers;
    }
    
    public function getTeachersForStudyProgramme($studyProgramme, $season) {
        $dql = 'SELECT DISTINCT t FROM AnketaBundle\Entity\UsersSubjects us, ' .
                'AnketaBundle\Entity\Subject s, ' .
                'AnketaBundle\Entity\TeachersSubjects ts, ' .
                'AnketaBundle\Entity\User t, ' .
                'AnketaBundle\Entity\Answer a ' .
                'WHERE us.subject = s ' .
                'AND ts.subject = s ' .
                'AND ts.teacher = t ' .
                'AND a.teacher = t ' .
                'AND a.season = :season ' .
                'AND us.season = :season ' .
                'AND ts.season = :season ' .
                'AND us.studyProgram = :studyProgramme ' . 
                'ORDER BY t.familyName';
        $teachers = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('studyProgramme' => $studyProgramme, 'season' => $season));
        return $teachers;
    }

    public function getTeachersForDepartment($department, $season) {
        $dql = 'SELECT DISTINCT t FROM AnketaBundle\Entity\Department d, ' .
                'AnketaBundle\Entity\User t, ' .
                'AnketaBundle\Entity\Answer a ' .
                'WHERE d = t.department ' .
                'AND a.teacher = t ' .
                'AND a.season = :season ' .
                'AND d = :department ' . 
                'ORDER BY t.familyName';
        $teachers = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('department' => $department, 'season' => $season));
        return $teachers;
    }

}