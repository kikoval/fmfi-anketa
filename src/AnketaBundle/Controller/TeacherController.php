<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeacherController extends Controller {

    /**
     * Search for teacher by name
     *
     * @throws AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response list of results in JSON format
     *
     * @todo limit numer of queries to protect abuse
     */
    public function searchAction()    {
        // only for authorized users
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        $name = $this->get('request')->get('name');
        if ($name == null) {
            return new Response('Required parameter "name" is missing.', 400);
        }

        $ldapSearch = $this->container->get('anketa.teacher_search');

        $result = $ldapSearch->byFullName($name);

        return new JsonResponse($result);
    }
}
