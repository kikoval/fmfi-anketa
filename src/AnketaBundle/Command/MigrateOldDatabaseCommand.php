<?php

/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Kralik <majak47@gmail.com>
 */

namespace AnketaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\DoctrineBundle\ConnectionFactory;
use PDO;

/**
 * Class functioning as command/task for migrating data from old database.
 *
 * @package    Anketa
 * @author     Martin Kralik <majak47@gmail.com>
 */
class MigrateOldDatabaseCommand extends ContainerAwareCommand {

    // v starej ma id 1, v novej to je pod 2
    private $oldSeasonId = 1;
    private $newSeasonId = 2;

    private $oldDB;
    private $newDB;

    private $output;

    protected function configure() {
        //parent::configure();

        $this->setName('anketa:migrate-old-db')
             ->setDescription('Presun data zo starej databazy do novej.')
             ->addArgument('old_db_name', InputArgument::REQUIRED, 'Name of the old database: ');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->output = $output;
        $this->newDB = $this->getContainer()->get('doctrine')->getEntityManager('default')->getConnection();
        $this->newDB->getConfiguration()->setSQLLogger(null);

        $connectionFactory = $this->getContainer()->get('doctrine.dbal.connection_factory');
        $params = $this->newDB->getParams();
        $params['dbname'] = $input->getArgument('old_db_name');
        $this->oldDB = $connectionFactory->createConnection($params);

        $this->newDB->setCharset('utf8');
        $this->oldDB->setCharset('utf8');

        $this->newDB->beginTransaction();
        try {
            $this->migrateUsersRoles();
            $this->migrateSubjects();
            $this->migrateQuestionsCategoriesChoices();
            $this->migrateTeachersResponses();
            $this->migrateAnswersUserSubjects();
        } catch (\Exception $e) {
            $this->newDB->rollback();
            $this->output->writeln($e->getTraceAsString());
            throw $e;
        }
        $this->newDB->commit();
    }

    // user, users_roles
    private function migrateUsersRoles() {
        // v starej DB su dve role, z toho AIS_STUDENT ma id = 2
        $result = $this->oldDB->executeQuery("SELECT u.*, MAX(ur.role_id)-1 as isStudent
                                              FROM `User` u
                                              JOIN `users_roles` ur ON (u.id = ur.user_id)
                                              GROUP BY u.id");
        $usersInserted = 0;
        $usersFound = 0;
        $userSeasonsInserted = 0;
        $userSeasonsFound = 0;

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $userId = $this->getNewUserId($row['userName']);
            if ($userId === false) {
                $this->newDB->insert('User', array(
                    'userName' => $row['userName'],
                    'displayName' => $row['displayName']
                ));
                $usersInserted++;
                $userId = $this->newDB->lastInsertId();
            } else {
                $usersFound++;
            }

            if (!$this->rowId('UserSeason', array('user_id' => $userId, 'season_id' => $this->newSeasonId))) {
                $this->newDB->insert('UserSeason', array(
                    'user_id' => $userId,
                    'season_id' => $this->newSeasonId,
                    'participated' => $row['participated'],
                    'finished' => 1,
                    'isStudent' => $row['isStudent'],
                    'isTeacher' => 0,
                ));
                $userSeasonsInserted++;
            } else {
                $userSeasonsFound++;
            }
        }
        $this->output->writeln('users: '.$usersInserted.' inserted | '.$usersFound.' found');
        $this->output->writeln('userseasons: '.$userSeasonsInserted.' inserted | '.$userSeasonsFound.' found');
    }

    // teacher, teachers_subjects, teachingassociation, response
    private function migrateTeachersResponses() {
        $teacherSubjectsInserted = 0;
        $teacherSubjectsFound = 0;

        $result = $this->oldDB->executeQuery("SELECT ts.*, s.code
                                              FROM teachers_subjects ts
                                              JOIN subject s ON (ts.subject_id = s.id)"
        );
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            if (!$this->rowId('Teacher', array('id' => $row['teacher_id']))) throw new \Exception('Teacher '.$row['id'].' '.$row['name']." not found!");
            $subjectId = $this->rowId('Subject', array('code' => $row['code']));
            if ($subjectId === false) throw new \Exception("Subject ".$row['code']." was not imported!");
            
            if (!$this->rowId('TeachersSubjects', array('subject_id' => $subjectId, 'teacher_id' => $row['teacher_id'], 'season_id' => $this->newSeasonId))) {
                $this->newDB->insert('TeachersSubjects', array(
                    'subject_id' => $subjectId,
                    'teacher_id' => $row['teacher_id'],
                    'season_id' => $this->newSeasonId,
                    'lecturer' => 0,
                    'trainer' => 0,
                ));
                $teacherSubjectsInserted++;
            } else {
                $teacherSubjectsFound++;
            }
        }
        $this->output->writeln('teacherSubjects: '.$teacherSubjectsInserted.' inserted | '.$teacherSubjectsFound.' found');


        $responsesInserted = 0;
        $responsesFound = 0;

        $result = $this->oldDB->executeQuery("SELECT r.*, s.code
                                              FROM Response r
                                              JOIN subject s ON (r.subject_id = s.id)"
        );
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            if ($row['teacher_id'] !== null) if (!$this->rowId('Teacher', array('id' => $row['teacher_id']))) throw new \Exception('Teacher '.$row['teacher_id'].' not found!');
            $subjectId = $this->rowId('Subject', array('code' => $row['code']));
            if ($subjectId === false) throw new \Exception("Subject ".$row['code']." was not imported!");
            if ($row['author_text'] === null) {
                $row['author_text'] = $this->oldDB->fetchColumn("SELECT displayName FROM `User` WHERE userName = ? LIMIT 1", array($row['author_login']),0);
            }

            if (!$this->rowId('Response', array('comment' => $row['comment'], 'season_id' => $this->newSeasonId))) {
                $this->newDB->insert('Response', array(
                    'subject_id' => $subjectId,
                    'teacher_id' => $row['teacher_id'],
                    'season_id' => $this->newSeasonId,
                    'studyProgram_id' => null,
                    'question_id' => $row['question_id'],
                    'comment' => $row['comment'],
                    'author_text' => $row['author_text'],
                    'author_login' => $row['author_login'],
                    'association' => null,
                ));
                $responsesInserted++;
            } else {
                $responsesFound++;
            }
        }
        $this->output->writeln('responses: '.$responsesInserted.' inserted | '.$responsesFound.' found');
    }

    // subject
    private function migrateSubjects() {
        $subjectsInserted = 0;
        $subjectsFound = 0;
        $subjectSeasonsInserted = 0;
        $subjectSeasonsFound = 0;

        $result = $this->oldDB->executeQuery("SELECT * FROM `subject`");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $subjectId = $this->rowId('Subject', array('code' => $row['code']));
            if ($subjectId === false) {
                $this->newDB->insert('Subject', array(
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'slug' => $row['code']
                ));
                $subjectsInserted++;
                $subjectId = $this->newDB->lastInsertId();
            } else {
                $subjectsFound++;
            }
              
            if (!$this->rowId('SubjectSeason', array('subject_id' => $subjectId, 'season_id' => $this->newSeasonId))) {
                $this->newDB->insert('SubjectSeason', array(
                    'subject_id' => $subjectId,
                    'season_id' => $this->newSeasonId,
                    'studentCountFaculty' => null,
                    'studentCountAll' => null,
                ));
                $subjectSeasonsInserted++;
            } else {
                $subjectSeasonsFound++;
            }
        }

        $this->output->writeln('subjects: '.$subjectsInserted.' inserted | '.$subjectsFound.' found');
        $this->output->writeln('subjectseasons: '.$subjectSeasonsInserted.' inserted | '.$subjectSeasonsFound.' found');
    }

    // users_subjects, answer
    private function migrateAnswersUserSubjects() {
        $userSubjectsInserted = 0;
        $userSubjectsFound = 0;

        $result = $this->oldDB->executeQuery("SELECT u.username, s.code
                                              FROM `users_subjects` us
                                              JOIN `User` u ON (u.id=us.user_id)
                                              JOIN `subject` s ON (s.id=us.subject_id)"
        );
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $userId = $this->getNewUserId($row['username']);
            $subjectId = $this->rowId('Subject', array('code' => $row['code']));
            if ($userId === false) throw new \Exception("User ".$row['username']." was not imported!");
            if ($subjectId === false) throw new \Exception("Subject ".$row['code']." was not imported!");
            
            if (!$this->rowId('UsersSubjects', array('subject_id' => $subjectId, 'user_id' => $userId, 'season_id' => $this->newSeasonId))) {
                $this->newDB->insert('UsersSubjects', array(
                    'user_id' => $userId,
                    'subject_id' => $subjectId,
                    'season_id' => $this->newSeasonId,
                    'studyProgram_id' => null,
                ));
                $userSubjectsInserted++;
            } else {
                $userSubjectsFound++;
            }
        }

        $this->output->writeln('userssubjects: '.$userSubjectsInserted.' inserted | '.$userSubjectsFound.' found');


        if ($this->rowId('Answer', array('season_id' => $this->newSeasonId))) {
            $this->output->writeln('answers already imported!');
            return;
        }

        $answersInserted = 0;
        $this->oldDB->getWrappedConnection()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $result = $this->oldDB->executeQuery("SELECT a.*, s.code, q.question, q.description, ch.choice
                                              FROM `Answer` a
                                              LEFT JOIN `subject` s ON (s.id=a.subject_id)
                                              JOIN `Question` q ON (a.question_id=q.id)
                                              LEFT JOIN `Choice` ch ON (a.option_id=ch.id)"
        );
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            if ($row['subject_id'] !== null) {
                $subjectId = $this->rowId('Subject', array('code' => $row['code']));
                if ($subjectId === false) throw new \Exception("Subject ".$row['code']." was not imported!");
            } else $subjectId = null;
            if ($row['teacher_id'] !== null) if (!$this->rowId('Teacher', array('id' => $row['teacher_id']))) throw new \Exception('Teacher '.$row['teacher_id'].' not found!');
            
            $questionId = $this->rowId('Question', array(
                'season_id' => $this->newSeasonId,
                'question' => $row['question'],
                'description' => $row['description']
            ));
            if ($questionId === false) throw new \Exception("Question \"".$row['question']."\" was not imported!");
            
            if ($row['option_id']) {
                $optionId = $this->rowId('Choice', array(
                    'question_id' => $questionId,
                    'choice' => $row['choice'],
                ));
                if ($questionId === false) throw new \Exception("Option \"".$row['option_id']."\" was not imported!");
            } else $optionId = null;

            $this->newDB->insert('Answer', array(
                'question_id' => $questionId,
                'subject_id' => $subjectId,
                'season_id' => $this->newSeasonId,
                'studyProgram_id' => null,
                'option_id' => $optionId,
                'teacher_id' => $row['teacher_id'],
                'author_id' => null,
                'evaluation' => $row['evaluation'],
                'comment' => $row['comment'],
                'attended' => $row['attended'],
                'inappropriate' => $row['inappropriate'],
            ));
            $answersInserted++;
        }

        $this->output->writeln('answers: '.$answersInserted.' inserted');

    }

    // question, choice, category
    private function migrateQuestionsCategoriesChoices() {
        $questionsInserted = 0;
        $questionsFound = 0;

        $categoryMapping = array(
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 5
        );

        $result = $this->oldDB->executeQuery("SELECT * FROM `Question` WHERE season_id = ?", array($this->oldSeasonId));
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $questionId = $this->rowId('Question', array(
                'season_id' => $this->newSeasonId,
                'question' => $row['question'],
                'description' => $row['description']
            ));
            if ($questionId === false) {
                $this->newDB->insert('Question', array(
                    'season_id' => $this->newSeasonId,
                    'category_id' => $categoryMapping[$row['category_id']],
                    'position' => $row['position'],
                    'title' => $row['title'],
                    'question' => $row['question'],
                    'description' => $row['description'],
                    'stars' => $row['stars'],
                    'hasComment' => $row['hasComment'],
                ));
                $questionsInserted++;
                $questionId = $this->newDB->lastInsertId();

                $result2 = $this->oldDB->executeQuery("SELECT * FROM `Choice` WHERE question_id = ?", array($row['id']));
                while ($choice = $result2->fetch(PDO::FETCH_ASSOC)) {
                    unset($choice['id']);
                    $choice['question_id'] = $questionId;
                    $this->newDB->insert('Choice', $choice);
                }
            } else {
                $questionsFound++;
            }

        }

        $this->output->writeln('questions: '.$questionsInserted.' inserted | '.$questionsFound.' found');
    }

    private function getNewUserId($login) {
        return $this->newDB->fetchColumn("SELECT id FROM `User` WHERE userName = ? LIMIT 1", array($login),0);
    }

    private function rowId($table, $data) {
        $conditions = array();
        $values = array();

        foreach ($data as $column => $value)
        {
            if ($value === null) $conditions[] = $column . " IS NULL";
            else {
                $conditions[] = $column . " = ?";
                $values[] = $value;
            }
        }

        $query = "SELECT id FROM `".$table."` WHERE ".  implode(" AND ", $conditions);
        $result = $this->newDB->fetchAssoc($query, $values);
        if ($result === false) return $result;
        return $result['id'];
    }

}