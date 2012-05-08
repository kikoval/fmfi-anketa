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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to import Teacher's given name and family name from LDAP
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */
class LDAPSearchCommand extends ContainerAwareCommand {

    protected function configure() {
        //parent::configure();

        $this
                ->setName('ldap:search')
                ->setDescription('Search for data in LDAP')
                ->addArgument('filter', InputArgument::REQUIRED)
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
        /* @var $ldap \AnketaBundle\Integration\LDAPRetriever */
        $ldap = $this->getContainer()->get('anketa.ldap_retriever');
        $ldap->loginIfNotAlready();

        $filter = $input->getArgument('filter');
        $ldapResult = $ldap->searchOne($filter,
                array('givenNameU8', 'snU8', 'displayName'));

        foreach ($ldapResult as $name => $vals) {
            foreach ($vals as $val) {
                $output->writeln($name . ': ' . $val);
            }
        }

        $ldap->logoutIfNotAlready();
    }

}