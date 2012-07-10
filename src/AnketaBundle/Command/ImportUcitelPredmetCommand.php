<?php

/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */

namespace AnketaBundle\Command;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AnketaBundle\Lib\SubjectIdentificationInterface;
use AnketaBundle\Lib\NativeCSVTableReader;
use AnketaBundle\Lib\FixedWidthTableReader;

/**
 * Class functioning as command/task for importing teachers, subjects,
 * and relationship between teachers and subjects from text file.
 *
 * @package    Anketa
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */
class ImportUcitelPredmetCommand extends AbstractImportCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:import:ucitel-predmet')
                ->setDescription('Importuj ucitelov predmety z textaku')
                ->addSeasonOption()
                ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Format of importted data', null)
        ;
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract class is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $subjectIdentification = $this->getContainer()->get('anketa.subject_identification');

        $fileFormat = $input->getOption('format');

        $season = $this->getSeason($input);
        $file = $this->openFile($input);

        if ($fileFormat == 'csv') {
            // nacitaj prve riadky, ktore nas nezaujimaju
            for ($i = 0;$i < 9; $i++) {
                fgets($file);
            }
            $tableReader = new NativeCSVTableReader($file);
        }
        else {
            $tableReader = new FixedWidthTableReader($file);
        }
        
        $conn = $this->getContainer()->get('database_connection');

        $conn->beginTransaction();

        $insertTeacher = $conn->prepare("
                    INSERT INTO Teacher (id, givenName, familyName, displayName, login) 
                    VALUES (:id, :givenName, :familyName, :displayName, :login) 
                    ON DUPLICATE KEY UPDATE login=login");

        $insertUser = $conn->prepare("
                    INSERT INTO User (displayName, userName) 
                    VALUES (:displayName, :login) 
		    ON DUPLICATE KEY UPDATE userName=userName");

        $insertSubject = $conn->prepare("
                    INSERT INTO Subject (code, name, slug)
                    VALUES (:code, :name, :slug)
                    ON DUPLICATE KEY UPDATE slug=slug");

        $insertTeacherSubject = $conn->prepare("
                    INSERT INTO TeachersSubjects (teacher_id, subject_id, season_id, lecturer, trainer) 
                    SELECT a.id, b.id, :season, :lecturer, :trainer
                    FROM Teacher a, Subject b 
                    WHERE a.login = :login and b.slug = :slug");

        try {
            while (($row = $tableReader->readRow()) !== false) {
                if ($fileFormat == 'csv') {
                    $id = $row[0];
                    $aisDlhyKod = $row[1];
                    $aisStredisko = $row[2];
                    $aisKratkyKod = $row[3];
                    $aisPopisRokVzniku = $row[4];
                    $aisNazov = $row[5];
                    $semester = $row[6];
                    $hodnost = $row[7];
                    $plneMeno = $row[8];
                    $priezvisko = $row[9];
                    $meno = $row[10];
                    $login = $row[11];
                }
                else {
                    $id = $row[0];
                    $aisKratkyKod = $row[1];
                    $aisStredisko = $row[2];
                    $aisPopisRokVzniku = $row[3];
                    $aisNazov = $row[4];
                    $login = $row[5];
                    $plneMeno = $row[6];
                    $hodnost = $row[7];
                    $priezvisko = '';
                    $meno = '';
                }

                if (strlen($aisNazov) == 0 || strlen($login) == 0 || strlen($plneMeno) == 0) {
                    continue;
                }

                $aisRokVzniku = substr($aisPopisRokVzniku, 2, 2);
                // TODO: Hmm, je toto spravne? (i.e. aj pre CSV, kde je priamo dlhy kod?)
                $aisDlhyKod = $aisStredisko . '/' . $aisKratkyKod . '/' . $aisRokVzniku;
                
                $props = $subjectIdentification->identify($aisDlhyKod, $aisNazov);
                $kod = $props['code'];
                $nazov = $props['name'];
                $slug = $props['slug'];

                $prednasajuci = 0;
                $cviciaci = 0;

                if ($hodnost == 'P') {
                    $prednasajuci = 1;
                } else if ($hodnost == 'C') {
                    $cviciaci = 1;
                } else {
                    continue;
                }

                $insertUser->bindValue('login', $login);
		$insertUser->bindValue('displayName', $plneMeno);
                $insertUser->execute();
                $userId = $conn->lastInsertId();
                
                $insertTeacher->bindValue('id', $userId);
                $insertTeacher->bindValue('displayName', $plneMeno);
                $insertTeacher->bindValue('givenName', $meno);
                $insertTeacher->bindValue('familyName', $priezvisko);
                $insertTeacher->bindValue('login', $login);
                $insertTeacher->execute();

                $insertSubject->bindValue('code', $kod);
                $insertSubject->bindValue('name', $nazov);
                $insertSubject->bindValue('slug', $slug);
                $insertSubject->execute();

                $insertTeacherSubject->bindValue('slug', $slug);
                $insertTeacherSubject->bindValue('login', $login);
                $insertTeacherSubject->bindValue('season', $season->getId());
                $insertTeacherSubject->bindValue('lecturer', $prednasajuci);
                $insertTeacherSubject->bindValue('trainer', $cviciaci);
                $insertTeacherSubject->execute();
            }
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $conn->commit();
        fclose($file);
    }

}
