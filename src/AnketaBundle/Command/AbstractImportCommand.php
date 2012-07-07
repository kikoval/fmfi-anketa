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
use AnketaBundle\Entity\Season;
use AnketaBundle\Entity\SeasonRepository;
use Exception;

/**
 * Abstract class implementing common code of import commands.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */
abstract class AbstractImportCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();

        $this
                ->addArgument('file', InputArgument::REQUIRED)
        ;
    }
    
    protected function addSeasonOption()
    {
        $this->addOption('season', 'd', InputOption::VALUE_OPTIONAL, 'Season to use', null);
        return $this;
    }
    
    /**
     * Get Season that was selected as option (or active season if none was specified)
     * @param InputInterface $input
     * @return Season
     * @throws Exception 
     */
    protected function getSeason(InputInterface $input)
    {
        $manager = $this->getContainer()->get('doctrine')->getEntityManager();
        $seasonSlug = $input->getOption('season');
        
        /** @var SeasonRepository seasonRepository */
        $seasonRepository = $manager->getRepository('AnketaBundle:Season');
        if ($seasonSlug === null) {
            $season = $seasonRepository->getActiveSeason();
            if ($season == null) {
                throw new Exception("V databaze sa nenasla aktivna Season");
            }
        } else {
            $season = $seasonRepository->findOneBy(array('slug' => $seasonSlug));
            if ($season == null) {
                throw new Exception("V databaze sa nenasla Season so slug " . $seasonSlug);
            }
        }
        return $season;
    }
    
    protected function openFile(InputInterface $input) {
        $filename = $input->getArgument('file');

        $file = fopen($filename, "r");
        if ($file === false) {
            throw new Exception('Failed to open file');
        }
        
        return $file;
    }

}