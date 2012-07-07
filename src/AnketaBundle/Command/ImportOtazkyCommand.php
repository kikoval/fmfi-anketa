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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class functioning as command/task for importing questions and categories
 * from YAML file.
 *
 * @package    Anketa
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */
class ImportOtazkyCommand extends ContainerAwareCommand {

    protected function configure() {
        //parent::configure();

        $this
                ->setName('anketa:import:otazky')
                ->setDescription('Importuj otazky z yaml')
                ->addArgument('file', InputArgument::REQUIRED)
                ->addOption('duplicates', 'c', InputOption::VALUE_NONE, 'Checks for Duplicate Categories', null)
                ->addOption('season', 'd', InputOption::VALUE_OPTIONAL, 'Season to use', null)
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
        $checkDuplicatesOption = $input->getOption('duplicates');

        $seasonSlug = $input->getOption('season');

        /** @var SeasonRepository seasonRepository */
        $seasonRepository = $manager->getRepository('AnketaBundle:Season');
        if ($seasonSlug === null) {
            $season = $seasonRepository->getActiveSeason();
            if ($season == null) {
                $output->writeln("<error>V databaze sa nenasla aktivna Season</error>");
                return;
            }
        } else {
            $season = $seasonRepository->findOneBy(array('slug' => $seasonSlug));
            if ($season == null) {
                $output->writeln("<error>V databaze sa nenasla Season so slug " . $seasonSlug . "</error>");
                return;
            }
        }

        $input_array = Yaml::parse($filename);

        // checkDuplicates
        if ($checkDuplicatesOption != null) {
            $this->checkDuplicates($input_array, $manager);
            return;
        }

        // spracuj kategorie
        $categories = $input_array["kategorie"];
        foreach ($categories as $category) {
            $this->processCategory($category, $manager);
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
    }

    private function processCategory(array $import, EntityManager $manager) {

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
        
        if ($objekt == null) {
            $manager->persist($category);
        } else {
            $spec = $import['id'];
            echo "Kategoria s unique indexom $spec  sa uz v databaze nachadza.\n";
        }
    }

    private function processQuestion(array $import, EntityManager $manager, Season $season, $questionPos) {

        // hlupy test, otazky su este zle navrhnute
        if ($import["text"] != '') {
            $question = new Question($import["text"]);
        } else {
            $question = new Question('defaultna otazka, chyba polozka text v anketa.yml');
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

        $question->setSeason($season);
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
            $kat = $sectionIdMap[$category['kategoria']];
            $typ = $category["popis"];
            $objekt = $categoryRepository->findOneBy(
                    array('type' => $kat,
                        'description' => $typ));
            if ($objekt == null) {
                echo 'null';
            } else {
                echo "Kategoria $kat s typom $typ sa uz v databaze nachadza.\n";
            }
        }
    }

}
