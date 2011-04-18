<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Controller__DataFixtures__ORM
 * @author     Jakub Markoš <jakub.markos@gmail.com>
 */

namespace AnketaBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Collections\ArrayCollection;
use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\Option;
use AnketaBundle\Entity\Category;
use AnketaBundle\Entity\Answer;
use AnketaBundle\Entity\Teacher;
use AnketaBundle\Entity\Subject;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\Role;

/**
 * Class for loading basic development data
 * 
 * @package    Anketa
 * @subpackage Anketa__Controller__DataFixtures__ORM
 * @author     Jakub Markoš <jakub.markos@gmail.com>
 */

class FixtureLoader implements FixtureInterface {
    
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param object $manager
     */
    public function load($manager) {
        // create categories
        $cat1 = new Category();
        $cat1->setCategory('general');
        $cat2 = new Category();
        $cat2->setCategory('subject');
        
        $manager->persist($cat1);
        $manager->persist($cat2);

        // create questions and their options
        $q1 = new Question('Ako sa vam paci nasa skola?');
        $q1->setCategory($cat1);

        // 1. samotne options nemusime persistovat, lebo Question ma nastavene
        // cascade=persist (aj remove) pre relaciu s Option - tzn doctrine
        // sa o to postara
        // 2. Question->addOption sa zaroven stara o druhu stranu relacie -
        // tzn zavola sa Option->setQuestion - aky je najlepsi sposob
        // dokumentovania takehoto spravania? aby kazdy nemusel poznat naspamat
        // tie entity classy
        $o1 = new Option('celkom pekna', 50);
        $o2 = new Option('nepaci sa mi', 0);
        $o3 = new Option('chcelo by to jedalen priamo na fakulte', 100);
        $q1->setOptions(new ArrayCollection(array('0' => $o1, $o2, $o3)));

        
        $q2 = new Question('Co nove ste sa dozvedeli na tomto predmete?');
        $q2->setCategory($cat2);
        $op1 = new Option('nic', 0);
        $op2 = new Option('velmi vela', 100);
        $q2->addOption($op1);
        $q2->addOption($op2);

        
        $q3 = new Question('Ohodnotte osvetlenie chodieb');
        $q3->setCategory($cat1);
        $q3->setStars(true);
        $q3->generateStarOptions();
        
        $manager->persist($q1);
        $manager->persist($q2);
        $manager->persist($q3);

        // create teacher + subject
        $t = new Teacher('Ucitel');
        $s = new Subject('Metalyza');

        // znova, teacher sa postara o update Subjectu
        $t->addSubject($s);

        // neni nastavene cascadovanie, kedze neviem ktorym smerom sa to
        // bude castejsie generovat - ci sa budu vyrabat predmety a k nim
        // pridavat ucitelia, alebo naopak
        // => takze musime najprv pridat ucitela, potom predmet
        $manager->persist($t);
        $manager->persist($s);

        // create answers
        $a = new Answer();
        $a->setQuestion($q2);
        $a->setComment('velmi podareny predmet');
        $a->setOption($op2);
        $a->setSubject($s);
        $a->setTeacher($t);

        $a2 = new Answer();
        $a2->setQuestion($q3);
        $a2->setComment('vecer byva obcas malo svetla');
        $a2->setOption($q3->getOptions()->get(2));

        $manager->persist($a);
        $manager->persist($a2);

        // create users, roles
        $u1 = new User('foo', 'Bc. Foo');
        $u2 = new User('admin', 'admin');
        $u3 = new User('sadmin', 'super_admin');
        $r1 = new Role('ROLE_ADMIN');
        $r2 = new Role('ROLE_USER');
        $r3 = new Role('ROLE_SUPER_ADMIN');
        
        $u1->addSubject($s);
        $u1->addRole($r2);

        $u2->addRole($r1);

        $u3->addRole($r3);

        $manager->persist($u1);
        $manager->persist($u2);
        $manager->persist($u3);
        $manager->persist($r1);
        $manager->persist($r2);
        $manager->persist($r3);


        $manager->flush();
    }
}