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
use AnketaBundle\Lib\NativeCSVTableReader;
use AnketaBundle\Lib\TableColumnResolver;
use AnketaBundle\Lib\OldPriFSubjectIdentification;
use AnketaBundle\Lib\SubjectIdentification;

/**
 * Migrate subject identification - codes and slugs
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */
class MigrateSubjectIdentificationCommand extends AbstractImportCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:migrate:subject-identification')
                ->setDescription('Zmigruj identifikaciu podla textaku')
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

        // nacitaj prve riadky, ktore nas nezaujimaju
        for ($i = 0;$i < 5; $i++) {
            fgets($file);
        }
        $tableReader = new NativeCSVTableReader($file);
        $tableResolver = new TableColumnResolver($tableReader);
        $tableResolver->mapColumnByTitle('Skratka', 'code');
        $tableResolver->mapColumnByTitle('Názov', 'name');
        
        $oldSubjectIdentification = new OldPriFSubjectIdentification();
        $newSubjectIdentification = new SubjectIdentification();
        
        $conn = $this->getContainer()->get('database_connection');

        $conn->beginTransaction();

        //$insertDepartment = $conn->prepare("
        //            INSERT INTO Department (code, name, homepage) 
        //            VALUES (:code, :name, :homepage)");
        
        $selectSubject = $conn->prepare('SELECT count(id) FROM Subject
            WHERE slug = :slug');

        try {
            $slugs = array();
            
            while (($row = $tableResolver->readRow()) !== false) {
                $code = $row['code'];
                $name = $row['name'];
                
                $oldProps = $oldSubjectIdentification->identify($code, $name);
                $newProps = $newSubjectIdentification->identify($code, $name);
                
                $selectSubject->bindValue('slug', $oldProps['slug']);
                $selectSubject->execute();
                $rs = $selectSubject->fetchAll(\PDO::FETCH_ASSOC);
                $output->writeln($rs[0]);
                
                $output->writeln($oldProps['code'] . '->' . $newProps['code']);
                $output->writeln($oldProps['slug'] . '->' . $newProps['slug']);
                $output->writeln('');
                
                $slugs[$oldProps['slug']] = true;
                
//                $insertDepartment->bindValue('code', $code);
//                $insertDepartment->bindValue('name', $name);
//                $insertDepartment->bindValue('homepage', $homepage);
//                $insertDepartment->execute();
                
            }
            
            $output->writeln('--');
            $sql = 'SELECT id, slug, name, code FROM Subject WHERE slug NOT IN (';
            foreach (array_keys($slugs) as $index => $slug) {
                if ($index > 0) $sql .= ', ';
                $sql .= $conn->quote($slug);
            }
            $sql .= ')';
            $select = $conn->prepare($sql);
            $select->execute();
            $bad = $select->fetchAll(\PDO::FETCH_ASSOC);
            $count = 0;
            foreach ($bad as $row) {
                $output->writeln($row['id']. ' ' . $row['slug']. ' ' . $row['name'] . ' ' . $row['code']);
                $count++;
            }
            $output->writeln('--');
            $output->writeln($count);
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $conn->commit();
        fclose($file);
    }

}