<?php
/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */

namespace AnketaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use AnketaBundle\DataFixtures\AnketaFixtures;

class LoadFixturesCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
            ->setName('anketa:fixtures:load')
            ->setDescription('Vygeneruje demo fixtures.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();

        if ($output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            $output = new NullOutput();
        }

        $fixtures = new AnketaFixtures($em, $output);

        $fixtures->createDepartments();

        $em->flush();
    }

}
