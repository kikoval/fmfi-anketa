<?php

namespace FMFI\AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $conn = $this->get('database_connection');
//        $users = $conn->fetchAll('SELECT * FROM otazky');
//        print_r($users);

        return $this->render('FMFIAnketaBundle:Default:index.html.twig');
    }
}
