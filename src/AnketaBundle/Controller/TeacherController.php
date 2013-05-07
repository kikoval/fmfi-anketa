<?php

namespace AnketaBundle\Controller;

use AnketaBundle\Integration\LDAPTeacherSearch;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeacherController extends Controller {
	
	private $ldapSearch = null;
	
	/**
	 * Search for teacher by name
	 * 
	 * @throws AccessDeniedException
	 * @return \Symfony\Component\HttpFoundation\Response list of results in JSON format
	 */
	public function searchAction()	{
		// only for authorized users
		if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
	        throw new AccessDeniedException();
	    }
		
		if ($this->ldapSearch == null) {
			$this->ldapSearch = new LDAPTeacherSearch(
									$this->container->get('anketa.ldap_retriever'),
									$this->container->getParameter('org_unit')
								);
		}
		
		// TODO limit queries only to anketa app
		$name = $this->get('request')->get('name');
		if ($name == null) return new Response('Required parameter "name" is missing.', 400);
		 
		$result = $this->ldapSearch->byFullName($name);
		
		$response = new Response();
		$response->setContent(json_encode($result));
		$response->headers->set('Content-Type', 'application/json');
		 
		return $response;
	}
}