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
use AnketaBundle\Lib\FixedWidthTableReader;
use AnketaBundle\Lib\TableColumnResolver;

/**
 * Class functioning as command/task for importing Teacher's department.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */
class ImportUcitelKatedraCommand extends AbstractImportCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:import:ucitel-katedra')
                ->setDescription('Importuj ucitel-katedry z textaku')
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
        
        $tableReader = new FixedWidthTableReader($file);
        $tableResolver = new TableColumnResolver($tableReader);
        $tableResolver->mapColumnByTitle('LOGIN', 'login');
        $tableResolver->mapColumnByTitle('SKRATKAORGANIZACNAJEDNOTKA', 'orgunit');
        
        $conn = $this->getContainer()->get('database_connection');

        $conn->beginTransaction();

        $updateTeacher = $conn->prepare("
                    UPDATE Teacher t, Department d
                    SET t.department_id = d.id
                    WHERE t.login = :login AND d.code = :department
                    ");

        try {
            while (($row = $tableResolver->readRow()) !== false) {
                $login = $row['login'];
                $orgUnit = $row['orgunit'];
                
                $updateTeacher->bindValue('login', $login);
                $updateTeacher->bindValue('department', $orgUnit);
                $updateTeacher->execute();
                
            }
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $conn->commit();
        fclose($file);
    }

}