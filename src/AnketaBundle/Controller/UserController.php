<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AnketaBundle\Entity\User;

class UserController extends Controller
{
    
    public function userBarAction() {
        $user = null;
        $token = $this->get('security.context')->getToken();
        if ($token !== null) {
            $user = $token->getUser();
        }
        if ($user === null || !($user instanceof User)) {
            return new Response();
        }
        $params = array();
        $params['username'] = $user->getUserName();
        $params['displayname'] = $user->getDisplayName();
        return $this->render('AnketaBundle:User:user_bar.html.twig',
                             $params);
    }

}
