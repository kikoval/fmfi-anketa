<?php
/**
 * @copyright Copyright (c) 2013 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Kralik <majak47@gmail.com>
 */

namespace AnketaBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to anonymize answers and sum voting summaries.
 *
 * @package    Anketa
 * @author     Martin Kralik <majak47@gmail.com>
 */
class AnonymizeAnswersCommand extends AbstractImportCommand {

    protected function configure() {
        $this
                ->setName('anketa:anonymizuj')
                ->setDescription('Anonymize answers and sum voting summaries.')
                ->addSeasonOption()
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
        $season = $this->getSeason($input);
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $em->getRepository('AnketaBundle:User')->anonymizeAllAnswersAndCreateSummaries($season);
        $output->writeln('All done!');
    }

}