<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AnketaController extends Controller
{
    public function indexAction()
    {
        return $this->render('AnketaBundle:Anketa:index.html.twig');
    }
}
