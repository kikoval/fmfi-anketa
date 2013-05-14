<?php

/**
 * @copyright Copyright (c) 2013 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Kralik <majak47@gmail.com>
 */

namespace AnketaBundle\Command;
use Doctrine\DBAL\Connection;
use AnketaBundle\Entity\Season;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class functioning as command/task for updating questions' and categories'
 * translations from YAML file.
 *
 * @package    Anketa
 * @author     Martin Kralik <majak47@gmail.com>
 */
class UpdatePrekladOtazokCommand extends AbstractImportCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:update:preklad:otazok')
                ->setDescription('Updatuj preklady otazok z yaml')
                ->addSeasonOption()
                ->addOption('dry-run', 'r', InputOption::VALUE_NONE, 'Dry run. Won\'t modify database.')
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
        $connection = $this->getContainer()->get('doctrine')->getEntityManager()->getConnection();
        $filename = $input->getArgument('file');
        $season = $this->getSeason($input);

        $connection->beginTransaction();
        $input_array = Yaml::parse($filename);

        $categories = $input_array["kategorie"];
        foreach ($categories as $category) {
            $this->processCategory($category, $connection);
        }

        $questions = $input_array["otazky"];
        foreach ($questions as $question) {
            $this->processQuestion($question, $connection, $season);
        }

        $dryRunOption = $input->getOption('dry-run');
        if (!$dryRunOption) {
            $connection->commit();
            $output->writeln('Done.');
        } else {
            $connection->rollback();
            $output->writeln('Nastaveny dry run - rollbackujem transakciu!');
        }
    }

    private function processCategory(array $import, Connection $connection) {
        $updateCategory = $connection->prepare('
            UPDATE Category
            SET description_en=:description_en
            WHERE description=:description;
        ');
        $updateCategory->bindValue('description_en', $import['popis_en']);
        $updateCategory->bindValue('description', $import['popis']);
        $updateCategory->execute();
    }

    private function processQuestion(array $import, Connection $connection, Season $season) {
        // aby sme vedeli porovnat NULL a mali jednoduchy kod pouzijem operator <=>
        $updateQuestion = $connection->prepare('
            UPDATE Question
            SET description_en=:description_en, question_en=:question_en
            WHERE description<=>:description AND question=:question AND season_id=:season_id;
        ');
        $updateQuestion->bindValue('description_en', $import['popis_en']);
        $updateQuestion->bindValue('description', $import['popis']);
        $updateQuestion->bindValue('question_en', $import['text_en']);
        $updateQuestion->bindValue('question', $import['text']);
        $updateQuestion->bindValue('season_id', $season->getId());
        $updateQuestion->execute();

        $updateOption = $connection->prepare('
            UPDATE Choice
            SET choice_en=:choice_en
            WHERE choice=:choice AND question_id IN (
                SELECT id
                FROM Question
                WHERE description<=>:description AND question=:question AND season_id=:season_id
            );
        ');
        $updateOption->bindValue('description', $import['popis']);
        $updateOption->bindValue('question', $import['text']);
        $updateOption->bindValue('season_id', $season->getId());

        if (array_key_exists("moznosti", $import)) {
            foreach ($import["moznosti"] as $option) {
                $updateOption->bindValue('choice', $option['text']);
                $updateOption->bindValue('choice_en', $option['text_en']);
                $updateOption->execute();
            }
        }
    }

}
