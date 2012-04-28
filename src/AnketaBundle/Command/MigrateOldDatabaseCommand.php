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
            $this->migrateAnswersUserSubjects();
            $this->migrateTeachersResponses();
        } catch (Exception $e) {
            $this->newDB->rollback();
            throw $e;
        }
        $this->newDB->commit();
    }

    // user, users_roles
    private function migrateUsersRoles() {
        // v starej DB su dve role, z toho AIS_STUDENT ma id = 2
        $result = $this->oldDB->executeQuery("SELECT u.*, MAX(ur.role_id)-1 as isStudent
                                              FROM `user` u
                                              JOIN `users_roles` ur ON (u.id = ur.user_id)
                                              GROUP BY u.id");
        $usersInserted = 0;
        $usersFound = 0;
        $userSeasonsInserted = 0;
        $userSeasonsFound = 0;

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $userId = $this->getNewUserId($row['userName']);
            if ($userId === false) {
                $this->newDB->insert('user', array(
                    'userName' => $row['userName'],
                    'displayName' => $row['displayName']
                ));
                $usersInserted++;
                $userId = $this->newDB->lastInsertId();
            } else {
                $usersFound++;
            }
            if (!$this->rowExists('userseason', array('user_id' => $userId, 'season_id' => $this->newSeasonId))) {
                $this->newDB->insert('userseason', array(
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
        //TODO
    }

    // subject
    private function migrateSubjects() {
        //TODO
    }

    // users_subjects, answer
    private function migrateAnswersUserSubjects() {
        //TODO
    }

    // question, choice, category
    private function migrateQuestionsCategoriesChoices() {
        //TODO
    }

    private function getNewUserId($login) {
        return $this->newDB->fetchColumn("SELECT id FROM `user` WHERE userName = ? LIMIT 1", array($login),0);
    }

    private function rowExists($table, $data) {
        $conditions = array();
        $values = array();

        foreach ($data as $column => $value)
        {
            $conditions[] = $column . " = ?";
            $values[] = $value;
        }

        $query = "SELECT * FROM `".$table."` WHERE ".  implode(" AND ", $conditions);
        $result = $this->newDB->fetchArray($query, $values);
        if ($result !== false) $result = true;
        return $result;
    }

}