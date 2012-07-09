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

/**
 * Class functioning as command/task for importing departments.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */
class ImportKatedraCommand extends AbstractImportCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:import:katedra')
                ->setDescription('Importuj katedry z textaku')
                ->addOption('parent', null, InputOption::VALUE_REQUIRED, 'Importovať iba katedry pod touto OJ')
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
        $parentFilter = $input->getOption('parent');

        // nacitaj prve riadky, ktore nas nezaujimaju
        for ($i = 0;$i < 5; $i++) {
            fgets($file);
        }
        $tableReader = new NativeCSVTableReader($file);
        
        $conn = $this->getContainer()->get('database_connection');

        $conn->beginTransaction();

        $insertDepartment = $conn->prepare("
                    INSERT INTO Department (code, name, homepage) 
                    VALUES (:code, :name, :homepage)");

        try {
            while (($row = $tableReader->readRow()) !== false) {
                $code = $row[0];
                $parentOrgUnit = $row[1];
                $type = $row[2];
                $name = $row[3];
                $homepage = $row[7];
                
                if ($type !== 'Kated') continue;
                if ($parentFilter !== null && $parentOrgUnit !== $parentFilter) {
                    continue;
                }
                
                if ($homepage === 'http://' || $homepage === '') {
                    $homepage = null;
                }
                
                $insertDepartment->bindValue('code', $code);
                $insertDepartment->bindValue('name', $name);
                $insertDepartment->bindValue('homepage', $homepage);
                $insertDepartment->execute();
                
            }
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $conn->commit();
        fclose($file);
    }

}