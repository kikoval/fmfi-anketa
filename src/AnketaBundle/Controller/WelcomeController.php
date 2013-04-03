<?php
/**
 * This file contains controller for public parts
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 *
 */

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WelcomeController extends Controller
{
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $activeSeason = $em->getRepository('AnketaBundle:Season')->getActiveSeason();
        $now = new \DateTime("now");
        $diff = $now->diff($activeSeason->getEndTime()); //difference between $now and $endTime
        if ($diff->format('%R') == '+') { //checks if $endTime is later than $now
            $activeSeason->timeToEnd = $diff;
        }else{
            $activeSeason->timeToEnd = null;
        }
        return $this->render('AnketaBundle:Welcome:index.html.twig',
            array('active_season' => $activeSeason));
    }

    public function faqAction()
    {
        return $this->render('AnketaBundle:Welcome:faq.html.twig');
    }

}
