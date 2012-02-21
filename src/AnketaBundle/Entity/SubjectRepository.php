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

        
    public function getSubjectsForTeacherForStudyProgramme($teacher, $studyProgramme, $season) {
        $dql = 'SELECT DISTINCT s FROM AnketaBundle\Entity\UsersSubjects us, ' .
                'AnketaBundle\Entity\Subject s, ' .
                'AnketaBundle\Entity\TeachersSubjects ts, ' .
                'AnketaBundle\Entity\Teacher t, ' .
                'AnketaBundle\Entity\Answer a ' .
                'WHERE us.subject = s ' .
                'AND ts.subject = s ' .
                'AND ts.teacher = t ' .
                'AND a.subject = s ' .
                'AND a.teacher = t ' .
                'AND us.season = :season ' .
                'AND ts.season = :season ' .
                'AND us.studyProgram = :studyProgramme ' . 
                'AND t = :teacher ' . 
                'ORDER BY s.name';
        $subjects = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('teacher' => $teacher, 'studyProgramme' => $studyProgramme, 'season' => $season));
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

    public function getSubjectsForStudyProgramme($studyProgramme, $season) {
        $dql = 'SELECT DISTINCT s FROM AnketaBundle\Entity\UsersSubjects us, ' .
                'AnketaBundle\Entity\Subject s, ' .
                'AnketaBundle\Entity\Answer a ' .
                'WHERE us.subject = s ' .
                'AND a.subject = s ' .
                'AND us.season = :season ' .
                'AND us.studyProgram = :studyProgramme ' . 
                'ORDER BY s.name';
        $subjects = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('studyProgramme' => $studyProgramme, 'season' => $season));
        return $subjects;
    }

    public function getSubjectsForDepartment($department, $season) {
        $dql = 'SELECT DISTINCT s FROM AnketaBundle\Entity\Department d, ' .
                'AnketaBundle\Entity\Subject s, ' .
                'AnketaBundle\Entity\SubjectSeason ss, ' .
                'AnketaBundle\Entity\SubjectSeasonDepartment ssd, ' .
                'AnketaBundle\Entity\Answer a ' .
                'WHERE d = ssd.department ' .
                'AND a.subject = s ' .
                'AND ss = ssd.subjectSeason ' .
                'AND ss.subject = s ' .
                'AND ss.season = :season ' .
                'AND a.season = :season ' .
                'AND d = :department ' . 
                'ORDER BY s.name';
        $subjects = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('department' => $department, 'season' => $season));
        return $subjects;
    }

}
