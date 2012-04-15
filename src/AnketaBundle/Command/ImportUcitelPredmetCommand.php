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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class functioning as command/task for importing teachers, subjects,
 * and relationship between teachers and subjects from text file.
 *
 * @package    Anketa
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */
class ImportUcitelPredmetCommand extends ContainerAwareCommand {

    protected function configure() {
        //parent::configure();

        $this
                ->setName('anketa:import-ucitel-predmet')
                ->setDescription('Importuj ucitelov predmety z textaku')
                ->addArgument('file', InputArgument::REQUIRED)
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

        $filename = $input->getArgument('file');

        $file = fopen($filename, "r");
        if ($file === false) {
            $output->writeln('<error>Failed to open file</error>');
            return;
        }

        // nacitaj prvy riadok
        $line = fgets($file);

        // splitni riadok 
        $splitnuty_riadok = preg_split("/[\s]+/", $line);
        $first_line_rows = array();

        // vytvor si pole objektov, ktore obsahuju udaje o nazve, zaciatku stlpca
        for ($i = 0; $i < count($splitnuty_riadok) - 1; $i++) {
            $position = strpos($line, $splitnuty_riadok[$i]);

            $row = new Stlpec();
            $row->setNazov($splitnuty_riadok[$i]);
            $row->setStart($position);

            $first_line_rows[] = $row;
        }

        // zisti dlzku stlpcov
        for ($i = 0; $i < count($first_line_rows) - 1; $i++) {
            $first_line_rows[$i]->setDlzka(
                    $first_line_rows[$i + 1]->getStart() - $first_line_rows[$i]->getStart()
            );
        }
        $first_line_rows[count($first_line_rows) - 1]->setDlzka(strlen($line) - $first_line_rows[count($first_line_rows) - 1]->getStart());

        $line = fgets($file); // nacitaj riadok s pomlckami (nepodstatny)

        $conn = $this->getContainer()->get('database_connection');

        $conn->beginTransaction();
        
        $insertTeacher = $conn->prepare("
                    INSERT INTO Teacher (displayName, login) 
                    VALUES (:displayName, :login) 
                    ON DUPLICATE KEY UPDATE login=login");
        
        $insertSubject = $conn->prepare("
                    INSERT INTO Subject (code, name) 
                    VALUES (:code, :name) 
                    ON DUPLICATE KEY UPDATE code=code");
        
        $insertTeacherSubject = $conn->prepare("
                    INSERT INTO TeachersSubjects (teacher_id, subject_id, season_id, lecturer, trainer) 
                    SELECT a.id, b.id, :season, :lecturer, :trainer
                    FROM Teacher a, Subject b 
                    WHERE a.login = :login and b.code = :code");

        try {
            while ($buffer = fgets($file)) {
                $id = trim(substr($buffer, $first_line_rows[0]->getStart(), $first_line_rows[0]->getDlzka()));
                $kod = trim(substr($buffer, $first_line_rows[1]->getStart(), $first_line_rows[1]->getDlzka()));
                $stredisko = trim(substr($buffer, $first_line_rows[2]->getStart(), $first_line_rows[2]->getDlzka()));
                $nazov = trim(substr($buffer, $first_line_rows[4]->getStart(), $first_line_rows[4]->getDlzka()));
                $login = trim(substr($buffer, $first_line_rows[5]->getStart(), $first_line_rows[5]->getDlzka()));
                $meno = trim(substr($buffer, $first_line_rows[6]->getStart(), $first_line_rows[6]->getDlzka()));
                $hodnost = trim(substr($buffer, $first_line_rows[7]->getStart(), $first_line_rows[7]->getDlzka()));

                if (strlen($nazov) == 0 || strlen($login) == 0 || strlen($meno) == 0) {
                    continue;
                }

                $prednasajuci = 0;
                $cviciaci = 0;
                
                if ($hodnost == 'P') {
                    $prednasajuci = 1;
                } else if ($hodnost == 'C') {
                    $cviciaci = 1;
                } else {
                    continue;
                }
                
                $insertTeacher->bindValue('displayName', $meno);
                $insertTeacher->bindValue('login', $login);
                $insertTeacher->execute();

                $insertSubject->bindValue('code', $kod);
                $insertSubject->bindValue('name', $nazov);
                $insertSubject->execute();

                $insertTeacherSubject->bindValue('code', $kod);
                $insertTeacherSubject->bindValue('login', $login);
                $insertTeacherSubject->bindValue('season', 1);
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

class Stlpec {

    private $nazov;
    private $dlzka;
    private $start;

    public function getStart() {
        return $this->start;
    }

    public function setStart($start) {
        $this->start = $start;
    }

    public function getNazov() {
        return $this->nazov;
    }

    public function setNazov($nazov) {
        $this->nazov = $nazov;
    }

    public function getDlzka() {
        return $this->dlzka;
    }

    public function setDlzka($dlzka) {
        $this->dlzka = $dlzka;
    }

    function __construct() {
        
    }

}