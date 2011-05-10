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

use AnketaBundle\Entity\Category;
use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\Option;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class functioning as command/task for importing questions and categories
 * from YAML file.
 *
 * @package    Anketa
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */
class ImportOtazkyCommand extends Command {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:import-otazky')
                ->setDescription('Importuj otazky z yaml')
                ->addArgument('file', InputArgument::REQUIRED)
                ->addOption('duplicates', 'c', InputOption::VALUE_NONE, 'Checks for Duplicate Categories', null)
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
        $checkDuplicatesOption = $input->getOption('duplicates');


        $manager = $this->container->get('doctrine.orm.entity_manager');
        $input_array = Yaml::load($filename);

        // checkDuplicates
        if ($checkDuplicatesOption != null) {
            $this->checkDuplicates($input_array, $manager);
            return;
        }

        // spracuj kategorie
        $categories = $input_array["kategorie"];
        // ass_array je pomocne asociativne pole pre referenciu Category objektov
        $ass_array = array();
        foreach ($categories as $category) {
            $ass_array = array_merge($ass_array, $this->processCategory($category, $manager));
        }

        // spracuj otazky
        $questions = $input_array["otazky"];
        foreach ($questions as $question) {
            $this->processQuestion($question, $manager, $ass_array);
        }

        $manager->flush();
    }

    private function processCategory(array $import, EntityManager $manager) {

        $sectionIdMap = array(
            'vseobecne' => 'general',
            'predmety' => 'subject',
            'studijnyprogram' => 'studijnyprogram'
        );

        $category = new Category($sectionIdMap[$import['kategoria']], $import["popis"]);

        $manager->persist($category);
        return array($import["id"] => $category);
    }

    private function processQuestion(array $import, EntityManager $manager, array $categories) {

        // hlupy test, otazky su este zle navrhnute
        if ($import["text"] != '') {
            $question = new Question($import["text"]);
        } else {
            $question = new Question('defaultna otazka, chyba polozka text v anketa.yml');
        }

        if (array_key_exists("popis", $import)) {
            $question->setDescription($import["popis"]);
        }

        $kat = $import["kategoria"];

        $question->setCategory($categories[$kat]);

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
            foreach ($import["moznosti"] as $option) {
                if (array_key_exists("hodnota", $option)) {
                    $hodnota = $option["hodnota"];
                } else {
                    $hodnota = 0;
                }
                $op = new Option(
                                $option["text"],
                                $hodnota);
                $question->addOption($op);
            }
        }
        $manager->persist($question);
    }

    private function checkDuplicates(array $import, EntityManager $manager) {
        $categories = $import["kategorie"];
        $questions = $import["otazky"];

        $sectionIdMap = array(
            'vseobecne' => 'general',
            'predmety' => 'subject',
            'studijnyprogram' => 'studijnyprogram'
        );
        $categoryRepository = $manager->getRepository('AnketaBundle\Entity\Category');
        foreach ($categories as $category) {
            $objekt = $categoryRepository->findOneBy(
                            array('category' => $sectionIdMap[$category['kategoria']],
                                'type' => $category["popis"]));
            $kat = $sectionIdMap[$category['kategoria']];
            $typ = $category["popis"];
            if ($objekt == null) {
                echo 'null';
            } else {
                echo "Kategoria $kat s typom $typ sa uz v databaze nachadza.\n";
            }
        }
    }
}