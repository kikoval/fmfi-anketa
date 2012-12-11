<?php
/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */

namespace AnketaBundle\DataFixtures;

use Symfony\Component\Console\Output\OutputInterface;
use AnketaBundle\Lib\Slugifier;
use AnketaBundle\Entity\CategoryType;
use AnketaBundle\Entity\Category;
use AnketaBundle\Entity\Department;
use AnketaBundle\Entity\StudyProgram;

class AnketaFixtures {

    public function __construct($em, OutputInterface $output) {
        $this->em = $em;
        $this->output = $output;
        $this->slugifier = new Slugifier();
    }

    protected static function generateName($words, $length) {
        $result = array();
        for ($i = 0; $i < $length; $i++) {
            $result[] = $words[array_rand($words)];
        }
        return implode(' ', $result);
    }

    protected static function makeAcronym($name) {
        $words = str_word_count($name, 1);
        $result = '';
        foreach($words as $word) $result .= $word[0];
        return strtoupper($result);
    }

    public function createDepartments() {
        $codes = array();
        $num = rand(8, 15);
        for ($i = 0; $i < $num; $i++) {
            do {
                $name = 'Katedra ' . self::generateName(self::$words, rand(2, 5));
                $code = self::makeAcronym($name);
            } while (isset($codes[$code]));
            $codes[$code] = true;

            $department = new Department();
            $department->setCode('FMFI.' . $code);
            $department->setName($name);
            $department->setHomepage('http://svt.fmph.uniba.sk/department/' . $code);
            $this->em->persist($department);
            $this->output->writeln('Department: ' . $name);
        }
    }

    public function createCategories() {
        $categories = array(
            new Category(CategoryType::SUBJECT, 'predmety', ''),
            new Category(CategoryType::TEACHER_SUBJECT, 'predmety_ucitel', ''),
            new Category(CategoryType::STUDY_PROGRAMME, 'studijnyprogram', ''),
            new Category(CategoryType::GENERAL, 'fakulta', 'Otázky ku fakulte'),
            new Category(CategoryType::GENERAL, 'ostatne', 'Ostatné'),
        );

        foreach ($categories as $category) {
            $this->em->persist($category);
            $this->output->writeln('Category: ' . $category->getSpecification());
        }
    }

    public function createStudyPrograms() {
        $codes = array();
        $num = rand(10, 30);
        for ($i = 0; $i < $num; $i++) {
            do {
                $name = $this->generateName(self::$words, rand(1, 6));
                $code = $this->makeAcronym($this->generateName(self::$words, rand(2, 4)));
                if (rand(0, 5) == 0) $code .= '/x';
            } while (isset($codes[$code]));
            $codes[$code] = true;
            $prefixes = array('');
            if (rand(0, 1)) $prefixes[] = 'm';
            if (rand(0, 1)) $prefixes[] = 'd';
            foreach ($prefixes as $prefix) {
                $program = new StudyProgram();
                $program->setName($name);
                $program->setCode($prefix . $code);
                $program->setSlug($this->slugifier->slugify($prefix . $code));
                $this->em->persist($program);
                $this->output->writeln('StudyProgram: ' . $name . ' (' . $prefix . $code . ')');
            }
        }
    }


    protected static $words = array(
        'lorem', 'ipsum', 'dolor', 'amet', 'consectetur', 'adipisicing',
        'elit', 'eiusmod', 'tempor', 'incididunt', 'labore', 'dolore',
        'magna', 'aliqua', 'enim', 'minim', 'veniam', 'quis', 'nostrud',
        'exercitation', 'ullamco', 'laboris', 'nisi', 'aliquip', 'commodo',
        'consequat', 'duis', 'aute', 'irure', 'dolor', 'reprehenderit',
        'voluptate', 'velit', 'esse', 'cillum', 'dolore', 'fugiat', 'nulla',
        'pariatur', 'excepteur', 'sint', 'occaecat', 'cupidatat', 'proident',
        'sunt', 'culpa', 'officia', 'deserunt', 'mollit', 'anim', 'laborum',
    );

    protected static $names = array(
        'Prokop', 'Blanka', 'Henrieta', 'Iveta', 'Vladislav', 'Ferdinand',
        'Estera', 'Radoslav', 'Fedor', 'Marcel', 'Laura', 'Radovan',
        'Vasil', 'Jaroslav', 'Emanuel', 'Berta', 'Viola', 'Jela', 'Dobromila',
        'Dagmara', 'Hortenzia', 'Nora', 'Marianna', 'Alica', 'Timotej', 'Alan',
        'Milan', 'Vavrinec', 'Lujza', 'Linda', 'Petronela', 'Daniel', 'Oskar',
        'Lea', 'Perla', 'Barbora', 'Arnold', 'Ela', 'Martina', 'Stanislava',
        'Frederik', 'Brigita', 'Irma', 'Gabriel', 'Tibor', 'Ema', 'Milota',
        'Marek', 'Zora', 'Andrea',
    );

    protected static $surnames = array(
        'A.', 'B.', 'C.', 'D.', 'E.', 'F.', 'G.', 'H.', 'I.', 'J.', 'K.',
        'L.', 'M.', 'N.', 'O.', 'P.', 'Q.', 'R.', 'S.', 'T.', 'U.', 'V.',
        'W.', 'X.', 'Y.', 'Z.',
    );

}
