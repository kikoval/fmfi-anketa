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
use AnketaBundle\Entity\CategoryType;
use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\Option;
use AnketaBundle\Entity\Season;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputOption;
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
class ImportOtazkyCommand extends AbstractImportCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('anketa:import:otazky')
                ->setDescription('Importuj otazky z yaml')
                ->addSeasonOption()
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
        $manager->getConnection()->beginTransaction();

        $season = $this->getSeason($input);

        $input_array = Yaml::parse($filename);

        // spracuj kategorie
        $categories = $input_array["kategorie"];
        foreach ($categories as $category) {
            $this->processCategory($category, $manager, $output);
        }
        $manager->flush();

        // spracuj otazky
        $questions = $input_array["otazky"];
        $questionPos = 0;
        $skippedQ = 0;
        foreach ($questions as $question) {
            if ($this->processQuestion($question, $manager, $season, $questionPos)) {
                $questionPos++;
            } else {
                $skippedQ++;
            }
        }
        $manager->flush();
        $output->writeln('Naimportovanych otazok: ' . $questionPos);
        $output->writeln('Preskocenych uz existujucich otazok: ' . $skippedQ);

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
        $category->setDescription($import["popis_en"], 'en');

        $categoryRepository = $manager->getRepository('AnketaBundle\Entity\Category');
        $objekt = $categoryRepository->findOneBy(
                array('specification' => $import['id']));

        if ($objekt == null) {
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

        $question->setQuestion($import['text_en'], 'en');
        $question->setPosition($questionPos);

        if (array_key_exists("popis", $import)) {
            $question->setDescription($import["popis"]);
            $question->setDescription($import["popis_en"], 'en');
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
                $op->setOption($option["text_en"], 'en');
                $question->addOption($op);
            }
        }

        $question->setIsSubjectEvaluation($this->checkBool($import, "hlavne_hodnotenie_predmetu"));
        $question->setIsTeacherEvaluation($this->checkBool($import, "hlavne_hodnotenie_ucitela"));

        $questionRepository = $manager->getRepository('AnketaBundle\Entity\Question');
        $objekt = $questionRepository->findOneBy(array(
            'season' => $season,
            'question' => $question->getQuestion(),
            'stars' => $question->getStars(),
            'hasComment' => $question->getHasComment(),
        ));

        if ($objekt != null) {
            // Aj ked tato otazka este nie je v DB uz o nej vie asociovana kategoria.
            // Toto prepojenie musime zrusit, inak nam to nepovoli otazku neulozit.
            $associatedQ = $question->getCategory()->getQuestions()->removeElement($question);
            return false;
        }

        $question->setSeason($season);
        $manager->persist($question);
        return true;
    }

    private function checkBool(array $arr, $key) {
        return array_key_exists($key, $arr) && $arr[$key] == 'Yes';
    }

}
