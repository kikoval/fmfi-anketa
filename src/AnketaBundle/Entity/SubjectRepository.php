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
                'AnketaBundle\Entity\User t, ' .
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

    /**
     * Vrati zoznam predmetov daneho ucitela, pre ktore je aspon jedna
     * odpoved o danom ucitelovi v danej season.
     * (t.j. nevrati predmety, kde su vyplnene otazky o predmete, ale nie o
     *  ucitelovi, napriek tomu ze ten predmet ucil)
     * @param User $teacher
     * @param Season $season
     * @return array(Subject)
     */
    public function getSubjectsForTeacherWithAnswersAboutTeacher($teacher, $season) {
        $dql = 'SELECT s FROM AnketaBundle\Entity\Subject s, ' .
                'AnketaBundle\Entity\Answer a, ' .
                'AnketaBundle\Entity\TeachersSubjects ts ' . 
                'WHERE s = ts.subject ' .
                'AND a.subject = s ' .
                'AND a.teacher = :teacher ' .
                'AND a.season = :season ' .
                'AND a.option is not null ' .
                'AND ts.teacher = :teacher ' .
                'AND ts.season = :season ' .
                'ORDER BY s.name';

        $subjects = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('teacher' => $teacher,
            'season' => $season));
        return $subjects;
    }
    
    /**
     * Vrati zoznam predmetov daneho ucitela, pre ktore je aspon jedna
     * odpoved o danom ucitelovi alebo predmete v danej season.
     * (t.j. vrati aj predmety, kde su vyplnene otazky o predmete, ale nie o
     *  ucitelovi, napriek tomu ze ten predmet ucil)
     * @param User $teacher
     * @param Season $season
     * @return array(Subject)
     */
    public function getSubjectsForTeacherWithAnyAnswers($teacher, $season) {
        $dql = 'SELECT s FROM AnketaBundle\Entity\Subject s, ' .
                'AnketaBundle\Entity\Answer a, ' .
                'AnketaBundle\Entity\TeachersSubjects ts ' . 
                'WHERE s = ts.subject ' .
                'AND a.subject = s ' .
                'AND a.season = :season ' .
                'AND a.option is not null ' .
                'AND ts.teacher = :teacher ' .
                'AND ts.season = :season ' .
                'ORDER BY s.name';

        $subjects = $this->getEntityManager()
                        ->createQuery($dql)->execute(array('teacher' => $teacher,
            'season' => $season));
        return $subjects;
    }
    
    public function getCategorizedSubjects(Season $season) {
        // najdeme subjecty, co maju aspon jednu odpoved
        $dql = 'SELECT DISTINCT s FROM AnketaBundle\Entity\Answer a, ' .
                'AnketaBundle\Entity\Subject s ' .
                'WHERE a.subject = s ' .
                'AND a.season = :season ' .
                'ORDER BY s.name';
        $subjects = $this->getEntityManager()
                         ->createQuery($dql)->execute(array('season' => $season));
        // TODO:nahrad celu tuto saskaren studijnymi programmi ked budu k dispozicii
        $categorized = array();
        $uncategorized = array();
        foreach ($subjects as $subject) {
            $category = $subject->getCategory();

            if ($category === Subject::NO_CATEGORY) {
                $uncategorized[] = $subject;
            } else {
                $categorized[$category][] = $subject;
            }
        }
        uksort($categorized, 'strcasecmp');
        // we want to append this after sorting
        if (!empty($uncategorized)) {
            $categorized[Subject::NO_CATEGORY] = $uncategorized;
        }
        return $categorized;
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
