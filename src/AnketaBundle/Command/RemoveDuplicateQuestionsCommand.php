<?php

/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Kralik <majak47@gmail.com>
 */

namespace AnketaBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use PDO;

/**
 * Class functioning as command/task for importing questions and categories
 * from YAML file.
 *
 * @package    Anketa
 * @author     Martin Kralik <majak47@gmail.com>
 */
class RemoveDuplicateQuestionsCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:remove-duplicate-questions')
                ->setDescription('Odstran zduplikovane otazky aj s odpovedami na ne.')
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
        /* @var $conn \Doctrine\DBAL\Connection */
        $conn = $this->getContainer()->get('database_connection');
        $conn->beginTransaction();

        // Predpokladame, ze vzdy pri ulozeni odpovede sa ulozi najprv odpoved
        // na duplikat a potom odpoved na orginal otazku.
        // Preto po tomto selecte dostaneme najprv odpovede na normalne otazky
        // (lebo ORDER BY question_id ASC) a potom na duplikaty,
        // pricom i-ty zaznam z kazdej casti je odpoved na otazku aj jej duplikat.
        $answers = $conn->executeQuery("SELECT *
                                        FROM `Answer`
                                        WHERE `season_id` = '2'
                                        ORDER BY `question_id` ASC, `id` ASC")
                                 ->fetchAll(PDO::FETCH_ASSOC);
        $half = count($answers) / 2;
        try {
            $index = 0;
            while ($index < $half) {
                if ($answers[$index]['question_id']+27 != $answers[$index+$half]['question_id']
                    || $answers[$index]['subject_id'] != $answers[$index+$half]['subject_id']
                    || $answers[$index]['teacher_id'] != $answers[$index+$half]['teacher_id']) {
                    // Ak pride k tomuto, tak neplati predpoklad hore.
                    throw new \Exception('Merging results won\'t be this easy!');
                }
                if ($answers[$index+$half]['evaluation'] != 0 || $answers[$index+$half]['comment'] !== null) {
                    // V tomto pripade je odpoved na duplikat ta spravna
                    // a preto ju dame do orginal odpovede.
                    if ($answers[$index+$half]['option_id'] !== null) {
                        // Aj moznosti odpovedi boli zduplikovane...
                        $answers[$index+$half]['option_id'] -= 103;
                    }
                    $conn->update(
                        'Answer',
                        array(
                            'evaluation' =>  $answers[$index+$half]['evaluation'],
                            'comment' =>  $answers[$index+$half]['comment'],
                            'option_id' =>  $answers[$index+$half]['option_id'],
                        ),
                        array('id' => $answers[$index]['id'])
                    );
                }
                $badId = $answers[$index+$half]['id'];
                $conn->executeQuery("DELETE FROM `Answer` WHERE `id` = ? LIMIT 1", array($badId));
                
                $index++;
            }

            // Teraz by sme nemali mat v DB ziadne odpovede na duplicitne otazky.
            $answers = $conn->executeQuery("SELECT *
                                            FROM `Answer`
                                            WHERE `season_id` = '2'
                                            AND `question_id` > 54
                                            ORDER BY `question_id` ASC, `id` ASC")
                            ->fetchAll();
            if (count($answers)) throw new \Excpetion('Impossible!');

            // Nakoniec postupne zmazme duplicitne otazky aj s ich moznostami.
            $conn->executeQuery("DELETE FROM `Choice` WHERE `question_id` > 54");
            $conn->executeQuery("DELETE FROM `Question` WHERE `id` > 54");
            
            $output->writeln("Done.");
        } catch (\Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $conn->commit();
    }

}