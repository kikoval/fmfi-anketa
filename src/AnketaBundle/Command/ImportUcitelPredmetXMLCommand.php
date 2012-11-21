<?php

/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk@gmail.com>
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
use AnketaBundle\Lib\RozvrhXMLImporter;
use Candle\RozvrhXMLParser;
use Exception;

/**
 * Class functioning as command/task for importing teachers, subjects,
 * and relationship between teachers and subjects from text file.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class ImportUcitelPredmetXMLCommand extends AbstractImportCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:import:ucitel-predmet-xml')
                ->setDescription('Importuj ucitelov predmety z xml-ka')
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
        $subjectIdentification = $this->getContainer()->get('anketa.subject_identification');

        $season = $this->getSeason($input);
        $file = $this->openFile($input);
        
        $conn = $this->getContainer()->get('database_connection')->getWrappedConnection();
        
        $importer = new RozvrhXMLImporter($conn, $subjectIdentification);
        $importer->prepareDatabase();
        
        $parser = new RozvrhXMLParser($importer);

        $conn->beginTransaction();

        $sql = 'CREATE TEMPORARY TABLE tmp_teachers_subjects ';
        $sql .= ' (teacher VARCHAR(50) binary not null collate utf8_bin, ';
        $sql .= ' subject VARCHAR(50) binary not null collate utf8_bin, ';
        $sql .= ' lecturer INTEGER not null, trainer INTEGER not null)';
        $conn->exec($sql);
        
        try {
            $importer->prepareTransaction();
            
            while (!feof($file)) {
                $data = fread($file, 4096);
                if ($data === false) {
                    throw new Exception('Failed reading file');
                }
                $parser->parse($data, false);
            }
            $parser->parse('', true);
            fclose($file);
            
            $sql = 'INSERT INTO Subject(code, name, slug) ';
            $sql .= ' SELECT code, name, slug ';
            $sql .= ' FROM tmp_insert_subject s ';
            $sql .= ' WHERE NOT EXISTS (SELECT s2.id FROM Subject s2 WHERE s2.slug = s.slug)';
            $conn->exec($sql);
            
            $sql = 'INSERT INTO User(displayName, userName) ';
            $sql .= " SELECT TRIM(CONCAT(given_name, ' ', family_name)),";
            $sql .= ' login FROM tmp_insert_teacher t';
            $sql .= ' WHERE t.login IS NOT NULL AND NOT EXISTS (SELECT u.id FROM User u WHERE u.userName = t.login)';
            $conn->exec($sql);
            
            $sql = 'INSERT INTO Teacher (id, givenName, familyName, displayName, login) ';
            $sql .= " SELECT u.id, t.given_name, t.family_name, NULL, t.login ";
            $sql .= " FROM tmp_insert_teacher t INNER JOIN User u ON u.userName = t.login";
            $sql .= ' WHERE t.login IS NOT NULL AND NOT EXISTS (SELECT tt.id FROM Teacher tt WHERE tt.login = t.login)';
            $conn->exec($sql);
            
            $sql = 'INSERT INTO tmp_teachers_subjects (teacher, subject, lecturer, trainer)';
            $sql .= ' SELECT DISTINCT tl.teacher_external_id, l.subject, 0, 0';
            $sql .= ' FROM tmp_insert_lesson l, tmp_insert_lesson_teacher tl';
            $sql .= ' WHERE tl.lesson_external_id = l.external_id';
            $conn->exec($sql);
            
            foreach (array('lecturer' => 'P', 'trainer' => 'C') as $column => $lt) {
                $sql = 'UPDATE tmp_teachers_subjects ts';
                $sql .= " SET ts.$column = 1";
                $sql .= ' WHERE EXISTS( ';
                $sql .=   'SELECT l.external_id FROM tmp_insert_lesson l, ';
                $sql .=   ' tmp_insert_lesson_teacher lt';
                $sql .=   ' WHERE ts.subject = l.subject AND l.external_id = lt.lesson_external_id';
                $sql .=   " AND lt.teacher_external_id = ts.teacher AND l.lesson_type = '$lt'";
                $sql .= ')';
                $conn->exec($sql);
            }
            
            $sql = 'INSERT INTO TeachersSubjects (teacher_id, subject_id, season_id, lecturer, trainer)';
            $sql .= ' SELECT t.id, s.id, :season, ts.lecturer, ts.trainer';
            $sql .= ' FROM Teacher t, Subject s, tmp_teachers_subjects ts, ';
            $sql .= ' tmp_insert_teacher tt, tmp_insert_subject ss ';
            $sql .= ' WHERE t.login = tt.login AND tt.external_id = ts.teacher ';
            $sql .= ' AND s.slug = ss.slug AND ss.external_id = ts.subject ';
            $prep = $conn->prepare($sql);
            $prep->execute(array('season' => $season->getId()));
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getTraceAsString() . '</error>');
            $conn->rollback();
            throw $e;
        }

        $conn->commit();
    }

}
