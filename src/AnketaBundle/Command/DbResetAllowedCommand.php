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

class DbResetAllowedCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('anketa:db-reset-allowed')
             ->setDescription('Zisti ci je zapnute allow_db_reset');
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
        return ($this->getContainer()->hasParameter('allow_db_reset') &&
            $this->getContainer()->getParameter('allow_db_reset') === true) ? 0 : 1;
    }

}
