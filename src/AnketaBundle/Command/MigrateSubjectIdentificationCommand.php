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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AnketaBundle\Lib\NativeCSVTableReader;
use AnketaBundle\Lib\ConcatTableReader;
use AnketaBundle\Lib\TableColumnResolver;
use AnketaBundle\Lib\OldPriFSubjectIdentification;
use AnketaBundle\Lib\SubjectIdentification;

/**
 * Migrate subject identification - codes and slugs
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */
class MigrateSubjectIdentificationCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:migrate:subject-identification')
                ->setDescription('Zmigruj identifikaciu podla textaku')
                ->addArgument('file', InputArgument::IS_ARRAY | InputArgument::REQUIRED)
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

        $filenames = $input->getArgument('file');
        
        $readers = array();
        foreach ($filenames as $filename) {
            $file = fopen($filename, "r");
            if ($file === false) {
                throw new Exception('Failed to open file: ' . $filename);
            }
            
            // nacitaj prve riadky, ktore nas nezaujimaju
            for ($i = 0;$i < 9; $i++) {
                fgets($file);
            }
            $readers[] = new NativeCSVTableReader($file);
        }

        $tableReader = new ConcatTableReader($readers);
        
        $tableResolver = new TableColumnResolver($tableReader);
        $tableResolver->mapColumnByTitle('Plná skratka', 'code');
        $tableResolver->mapColumnByTitle('Názov', 'name');
        
        $oldSubjectIdentification = new OldPriFSubjectIdentification();
        $newSubjectIdentification = new SubjectIdentification();
        
        $conn = $this->getContainer()->get('database_connection');

        $conn->beginTransaction();
        
        $insertUpdate = $conn->prepare('
            INSERT INTO update_subjects VALUES (:old_code, :old_slug, :new_code, :new_slug)');

        $updateSubject = $conn->prepare("
                    UPDATE Subject SET slug=:new_slug, code=:new_code WHERE slug=:old_slug");
        
        $selectSubject = $conn->prepare('SELECT count(id) AS pocet FROM Subject
            WHERE slug = :slug AND code = :code');

        try {
            $slugs = array();
            $subjects = array();
            
            while (($row = $tableResolver->readRow()) !== false) {
                $code = $row['code'];
                $name = $row['name'];
                
                $subjects[$code] = $name;
            }
            
            $notInDB = 0;
            $inDB = 0;
            
            $updates = array();
            
            foreach ($subjects as $code => $name) {
                $output->writeln($code);
                $oldProps = $oldSubjectIdentification->identify($code, $name);
                $newProps = $newSubjectIdentification->identify($code, $name);
                
                $selectSubject->bindValue('slug', $oldProps['slug']);
                $selectSubject->bindValue('code', $oldProps['code']);
                $selectSubject->execute();
                $rs = $selectSubject->fetchAll(\PDO::FETCH_ASSOC);
                
                if ($rs[0]['pocet'] == 1) {
                    $inDB++;
                }
                else {
                    $notInDB++;
                }
                
                $updates[] = array('old_slug' => $oldProps['slug'],
                    'old_code' => $oldProps['code'],
                    'new_slug' => $newProps['slug'],
                    'new_code' => $newProps['code']);
                
                $slugs[$oldProps['slug']] = true;
                                
            }
            
            $sql = 'SELECT s.id, s.slug, s.name, s.code, COUNT(a.id) as pocet FROM Subject s LEFT JOIN Answer a ON a.subject_id = s.id WHERE s.slug NOT IN (';
            foreach (array_keys($slugs) as $index => $slug) {
                if ($index > 0) $sql .= ', ';
                $sql .= $conn->quote($slug);
            }
            $sql .= ') GROUP BY s.id, s.slug, s.name, s.code';
            $select = $conn->prepare($sql);
            $select->execute();
            $bad = $select->fetchAll(\PDO::FETCH_ASSOC);
            $notInExport = 0;
            $noAnswers = 0;
            foreach ($bad as $row) {
                $output->writeln($row['id']. "\t" . $row['pocet'] . "\t" . $row['slug'].
                        "\t" . $row['name'] . "\t" . $row['code'] );
                $notInExport++;
                if ($row['pocet'] == 0) {
                    $noAnswers++;
                }
            }
            
            $output->writeln('------');
            
            $updated = 0;
            
            foreach ($updates as $update) {
                
                $updateSubject->bindValue('old_slug', $update['old_slug']);
                $updateSubject->bindValue('new_slug', $update['new_slug']);
                $updateSubject->bindValue('new_code', $update['new_code']);
                $updateSubject->execute();
                $updated += $updateSubject->rowCount();
                if ($updateSubject->rowCount() == 0) {
                    $output->writeln($update['old_slug'] . "\t" . $update['old_code'] . "\t" .
                        $update['new_slug'] . "\t" . $update['new_code']);
                }
            }
            
            $output->writeln('------');
            $output->writeln('Mame v DB, ale nenasli sa v exporte: '.$notInExport . '(' . $noAnswers . ' bez odpovedi)');
            $output->writeln('Mame v exporte, ale nenasli sa v DB: '.$notInDB);
            $output->writeln('Mame v exporte a nasli sa v DB: '.$inDB);
            $output->writeln('Pocet updatov: '.count($updates));
            $output->writeln('Updatli sa v DB: '.$updated);

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $conn->commit();
        fclose($file);
    }

}