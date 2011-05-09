<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AnketaController extends Controller
{
    public function indexAction()
    {
        // NOTE: tu by bol rozcestnik vyplnanie / statistiky / mozno nieco dalsie
        // kedze zatial mame iba vyplnanie, proste dame redirect
        // TODO: toto chceme aby rovno redirectovalo na prvu ne-100% sekciu
        return new RedirectResponse($this->generateUrl('answer'));
    }
}
