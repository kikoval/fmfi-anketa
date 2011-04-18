<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AnketaController extends Controller
{
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $username = $user->getUsername();
        return $this->render('AnketaBundle:Anketa:index.html.twig',
                             array('username'=>$username));
    }
}
