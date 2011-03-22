<?php

namespace FMFI\SVT\AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FMFISVTAnketaBundle:Default:index.html.twig');
    }
}
