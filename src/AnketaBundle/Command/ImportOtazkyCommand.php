<?php

/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */

namespace AnketaBundle\Command;

use DateTime;
use AnketaBundle\Entity\Category;
use AnketaBundle\Entity\CategoryType;
use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\Option;
use AnketaBundle\Entity\Season;
use AnketaBundle\Entity\SeasonRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\DataFixtures\FixtureInterface;

/**
 * Class functioning as command/task for importing questions and categories
 * from YAML file.
 *
 * @package    Anketa
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */
class ImportOtazkyCommand extends AbstractImportCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:import:otazky')
                ->setDescription('Importuj otazky z yaml')
                ->addSeasonOption()
                ->addOption('no-duplicates-check', 'c', InputOption::VALUE_OPTIONAL, 'Don\'t check for duplicate categories', null)
                ->addOption('dry-run', 'r', InputOption::VALUE_NONE, 'Dry run. Won\'t modify database')
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
        $manager = $this->getContainer()->get('doctrine')->getEntityManager();
        $filename = $input->getArgument('file');
        $checkDuplicatesOption = $input->getOption('no-duplicates-check');
        $manager->getConnection()->beginTransaction();

        $season = $this->getSeason($input);

        $input_array = Yaml::parse($filename);

        // checkDuplicates
        if ($checkDuplicatesOption === null) {
            $this->checkDuplicates($input_array, $manager, $output);
            return;
        }

        // spracuj kategorie
        $categories = $input_array["kategorie"];
        foreach ($categories as $category) {
            $this->processCategory($category, $manager, $output);
        }
        $manager->flush();
	
        // spracuj otazky
        $questions = $input_array["otazky"];
        $questionPos = 0;
        foreach ($questions as $question) {
            $this->processQuestion($question, $manager, $season, $questionPos);
            $questionPos++;
        }
        $manager->flush();
        $output->writeln('Naimportovanych otazok: '.$questionPos);

        $dryRunOption = $input->getOption('dry-run');
        if (!$dryRunOption) {
            $manager->getConnection()->commit();
        } else {
            $manager->getConnection()->rollback();
            $output->writeln('Nastaveny dry run - rollbackujem transakciu!');
        }
    }

    private function processCategory(array $import, EntityManager $manager, OutputInterface $output) {

        $sectionIdMap = array(
            'vseobecne' => CategoryType::GENERAL,
            'predmety' => CategoryType::SUBJECT,
            'predmety_ucitel' => CategoryType::TEACHER_SUBJECT,
            'studijnyprogram' => CategoryType::STUDY_PROGRAMME,
        );

        $category = new Category($sectionIdMap[$import['kategoria']], $import['id'], $import["popis"]);

        $categoryRepository = $manager->getRepository('AnketaBundle\Entity\Category');
        $objekt = $categoryRepository->findOneBy(
                array('specification' => $import['id']));
        
        if (!$objekt) {
            $manager->persist($category);
        } else {
            $spec = $import['id'];
            $output->writeln('Kategoria s unique indexom '.$spec.' sa uz v databaze nachadza.');
        }
    }

    private function processQuestion(array $import, EntityManager $manager, Season $season, $questionPos) {

        // hlupy test, otazky su este zle navrhnute
        if ($import["text"] != '') {
            $question = new Question($import["text"]);
        } else {
            throw new Exception('chyba polozka text vo vstupnom yml subore');
        }

        $question->setPosition($questionPos);

        if (array_key_exists("popis", $import)) {
            $question->setDescription($import["popis"]);
        }
        
        $categoryRepository = $manager->getRepository('AnketaBundle\Entity\Category');
        $category = $categoryRepository->findOneBy(
                array('specification' => $import['kategoria']));

        $question->setCategory($category);

        if ($import["komentar"] == 'No') {
            $question->setHasComment(false);
        } else {
            $question->setHasComment(true);
        }

        if ($import["hviezdicky"] == 'Yes') {
            $question->setStars(true);
            $question->generateStarOptions();
        } else {
            $question->setStars(false);
        }
        if (array_key_exists("moznosti", $import)) {
            $pos = 0;
            foreach ($import["moznosti"] as $option) {
                if (array_key_exists("hodnota", $option)) {
                    $hodnota = $option["hodnota"];
                } else {
                    $hodnota = 0;
                }
                $op = new Option(
                                $option["text"],
                                $hodnota,
                                $pos++);
                $question->addOption($op);
            }
        }
        if (array_key_exists("hlavne_hodnotenie_predmetu", $import)
            && $import["hlavne_hodnotenie_predmetu"] == "Yes") {
            $question->setIsSubjectEvaluation(true);
        } else {
            $question->setIsSubjectEvaluation(false);
        }
        if (array_key_exists("hlavne_hodnotenie_vyucujuceho", $import)
            && $import["hlavne_hodnotenie_vyucujuceho"] == "Yes") {
            $question->setIsTeacherEvaluation(true);
        } else {
            $question->setIsTeacherEvaluation(false);
        }

        $question->setSeason($season);
        $manager->persist($question);
    }

    private function checkDuplicates(array $import, EntityManager $manager, OutputInterface $output) {
        $categories = $import["kategorie"];
        $questions = $import["otazky"];

        $sectionIdMap = array(
            'vseobecne' => CategoryType::GENERAL,
            'predmety' => CategoryType::SUBJECT,
            'predmety_ucitel' => CategoryType::TEACHER_SUBJECT,
            'studijnyprogram' => CategoryType::STUDY_PROGRAMME,
        );
        $categoryRepository = $manager->getRepository('AnketaBundle\Entity\Category');
        foreach ($categories as $category) {
            $kat = $sectionIdMap[$category['kategoria']];
            $typ = $category["popis"];
            $objekt = $categoryRepository->findOneBy(
                    array('type' => $kat,
                        'description' => $typ));
            if (!$objekt) {
                $output->writeln('Kategoria '.$kat.' s typom '.$typ.' sa v databaze NEnachadza.');
            } else {
                $output->writeln('Kategoria '.$kat.' s typom '.$typ.' sa uz v databaze nachadza.');
            }
        }
    }

}
