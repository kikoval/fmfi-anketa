<?php

/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */

namespace AnketaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AnketaBundle\Entity\Season;
use AnketaBundle\Entity\SeasonRepository;
use AnketaBundle\Lib\SubjectIdentificationInterface;

/**
 * Class functioning as command/task for importing teachers, subjects,
 * and relationship between teachers and subjects from text file.
 *
 * @package    Anketa
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */
class ImportUcitelPredmetCommand extends ContainerAwareCommand {

    protected function configure() {
        //parent::configure();

        $this
                ->setName('anketa:import-ucitel-predmet')
                ->setDescription('Importuj ucitelov predmety z textaku')
                ->addArgument('file', InputArgument::REQUIRED)
                ->addOption('season', null, InputOption::VALUE_OPTIONAL, 'Season to use', null)
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
        
        $subjectIdentification = $this->getContainer()->get('anketa.subject_identification');
        
        $seasonSlug = $input->getOption('season');

        /** @var SeasonRepository seasonRepository */
        $seasonRepository = $manager->getRepository('AnketaBundle:Season');
        if ($seasonSlug === null) {
            $season = $seasonRepository->getActiveSeason();
            if ($season == null) {
                $output->writeln("<error>V databaze sa nenasla aktivna Season</error>");
                return;
            }
        }
        else {
            $season = $seasonRepository->findOneBy(array('slug' => $seasonSlug));
            if ($season == null) {
                $output->writeln("<error>V databaze sa nenasla Season so slug " . $seasonSlug. "</error>");
                return;
            }
        }

        $filename = $input->getArgument('file');

        $file = fopen($filename, "r");
        if ($file === false) {
            $output->writeln('<error>Failed to open file</error>');
            return;
        }

        // nacitaj prvy riadok
        $line = fgets($file);

        // splitni riadok 
        $splitnuty_riadok = preg_split("/[\s]+/", $line);
        $stlpce = array();

        // vytvor si pole objektov, ktore obsahuju udaje o nazve, zaciatku stlpca
        for ($i = 0; $i < count($splitnuty_riadok) - 1; $i++) {
            $position = strpos($line, $splitnuty_riadok[$i]);

            $stlpec = new Stlpec();
            $stlpec->setNazov($splitnuty_riadok[$i]);
            $stlpec->setStart($position);

            $stlpce[] = $stlpec;
        }

        // zisti dlzku stlpcov
        for ($i = 0; $i < count($stlpce) - 1; $i++) {
            $stlpce[$i]->setDlzka(
                    $stlpce[$i + 1]->getStart() - $stlpce[$i]->getStart()
            );
        }
        $stlpce[count($stlpce) - 1]->setDlzka(strlen($line) - $stlpce[count($stlpce) - 1]->getStart());

        $line = fgets($file); // nacitaj riadok s pomlckami (nepodstatny)

        $conn = $this->getContainer()->get('database_connection');

        $conn->beginTransaction();
        
        $insertTeacher = $conn->prepare("
                    INSERT INTO Teacher (displayName, login) 
                    VALUES (:displayName, :login) 
                    ON DUPLICATE KEY UPDATE login=login");

        $insertUser = $conn->prepare("
                    INSERT INTO User (id, displayName, userName) 
                    VALUES (:id, :displayName, :login) 
		    ON DUPLICATE KEY UPDATE userName=userName");

        $insertSubject = $conn->prepare("
                    INSERT INTO Subject (code, name, slug)
                    VALUES (:code, :name, :slug)
                    ON DUPLICATE KEY UPDATE slug=slug");
        
        $insertTeacherSubject = $conn->prepare("
                    INSERT INTO TeachersSubjects (teacher_id, subject_id, season_id, lecturer, trainer) 
                    SELECT a.id, b.id, :season, :lecturer, :trainer
                    FROM Teacher a, Subject b 
                    WHERE a.login = :login and b.slug = :slug");

        try {
            while ($buffer = fgets($file)) {
                $id = $stlpce[0]->extractData($buffer);
                $aisKod = $stlpce[1]->extractData($buffer);
                $aisStredisko = $stlpce[2]->extractData($buffer);
                $aisNazov = $stlpce[4]->extractData($buffer);
                $login = $stlpce[5]->extractData($buffer);
                $meno = $stlpce[6]->extractData($buffer);
                $hodnost = $stlpce[7]->extractData($buffer);

                if (strlen($aisNazov) == 0 || strlen($login) == 0 || strlen($meno) == 0) {
                    continue;
                }

                $props = $subjectIdentification->identify($aisStredisko.'/'.$aisKod, $aisNazov);
                $kod = $props['code'];
                $nazov = $props['name'];
                $slug = $props['slug'];

                $prednasajuci = 0;
                $cviciaci = 0;
                
                if ($hodnost == 'P') {
                    $prednasajuci = 1;
                } else if ($hodnost == 'C') {
                    $cviciaci = 1;
                } else {
                    continue;
                }
                
                $insertTeacher->bindValue('displayName', $meno);
                $insertTeacher->bindValue('login', $login);
                $insertTeacher->execute();

		$faggot = $conn->lastInsertId();

		$insertUser->bindValue('id', $faggot);
                $insertUser->bindValue('login', $login);
		$insertUser->bindValue('displayName',$meno);
                $insertUser->execute();

                $insertSubject->bindValue('code', $kod);
                $insertSubject->bindValue('name', $nazov);
                $insertSubject->bindValue('slug', $slug);
                $insertSubject->execute();

                $insertTeacherSubject->bindValue('slug', $slug);
                $insertTeacherSubject->bindValue('login', $login);
                $insertTeacherSubject->bindValue('season', $season->getId());
                $insertTeacherSubject->bindValue('lecturer', $prednasajuci);
                $insertTeacherSubject->bindValue('trainer', $cviciaci);
                $insertTeacherSubject->execute();
            }
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $conn->commit();
        fclose($file);
    }

}

class Stlpec {

    private $nazov;
    private $dlzka;
    private $start;

    public function getStart() {
        return $this->start;
    }

    public function setStart($start) {
        $this->start = $start;
    }

    public function getNazov() {
        return $this->nazov;
    }

    public function setNazov($nazov) {
        $this->nazov = $nazov;
    }

    public function getDlzka() {
        return $this->dlzka;
    }

    public function setDlzka($dlzka) {
        $this->dlzka = $dlzka;
    }
    
    /**
     * Vyber data stlpca z riadku
     * @param string $line
     * @return string hodnota stlpca
     */
    public function extractData($line) {
        return trim(substr($line, $this->getStart(), $this->getDlzka()));
    }

    function __construct() {
        
    }

}
