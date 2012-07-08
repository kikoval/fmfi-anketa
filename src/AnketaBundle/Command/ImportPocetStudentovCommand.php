<?php

/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Command;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AnketaBundle\Lib\AISDelimitedTableReader;

/**
 * Class functioning as command/task for importing departments.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */
class ImportPocetStudentovCommand extends AbstractImportCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:import:pocet-studentov')
                ->setDescription('Importuj pocet studentov z textaku')
                ->addArgument('column', InputArgument::REQUIRED, 'faculty|all')
                ->addOption('dump-sql', null, InputOption::VALUE_NONE, 'Whether to dump SQL instead of executing')
                ->addSeasonOption()
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
        
        $file = $this->openFile($input);
        if ($input->getArgument('column') === 'faculty') {
            $column = 'studentCountFaculty';
        }
        else if ($input->getArgument('column') === 'all') {
            $column = 'studentCountAll';
        }
        else {
            $output->writeln('<error>Invalid value for column. Use either faculty or all</error>');
            return;
        }
        if (preg_match('/^\d+$/', $input->getOption('season'))) {
            $season_id = intval($input->getOption('season'));
        }
        else {
            $season_id = $this->getSeason($input)->getId();
        }
        
        $subjectIdentification = $this->getContainer()->get('anketa.subject_identification');
        $tableReader = new AISDelimitedTableReader($file);
        
        $conn = $this->getContainer()->get('database_connection');

        $conn->beginTransaction();
        
        $dumpSQL = $input->getOption('dump-sql');
        
        if (!$dumpSQL) {
            $insertSubjectSeason = $conn->prepare("
                    INSERT INTO SubjectSeason (subject_id, season_id, $column)
                    SELECT s.id, :season, :count FROM Subject s WHERE s.slug = :subject_slug
                    ON DUPLICATE KEY UPDATE $column = VALUES($column)
                    ");
        }
        else {
            $insertTemplate = "INSERT INTO SubjectSeason (subject_id, season_id, $column) " .
                    "SELECT s.id, %d, %d FROM Subject s WHERE s.slug = %s " .
                    "ON DUPLICATE KEY UPDATE $column = VALUES($column);";
        }

        try {
            $pocty = array();
            
            while (($row = $tableReader->readRow()) !== false) {
                $level = $row[0];
                if ($level !== '0') continue;
                
                $uoc = $row[1];
                $subjectCode = $row[2];
                $studyProgramCode = $row[5];
                
                $props = $subjectIdentification->identify($subjectCode, null);
                $kod = $props['code'];
                $slug = $props['slug'];
                
                if (!isset($pocty[$slug])) {
                    $pocty[$slug] = array();
                }
                
                if (!isset($pocty[$slug][$uoc])) {
                    $pocty[$slug][$uoc] = true;
                }
                
            }
            
            if ($dumpSQL) {
                foreach ($pocty as $slug => $students) {
                    $output->writeln(sprintf($insertTemplate, $season_id, count($students), $conn->quote($slug)));
                }
            }
            else {
                foreach ($pocty as $slug => $students) {
                    $insertSubjectSeason->bindValue('subject_slug', $slug);
                    $insertSubjectSeason->bindValue('count', count($students));
                    $insertSubjectSeason->bindValue('season', $season_id);
                    $insertSubjectSeason->execute();

                    if ($insertSubjectSeason->rowCount() == 0) {
                        $output->writeln(sprintf('Predmet %s nie je v databaze, nevytvaram SubjectSeason.', $slug));
                    }
                }
            }
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $conn->commit();
        fclose($file);
    }

}