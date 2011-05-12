<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HlasovanieController extends Controller
{
    public function indexAction()
    {
        // NOTE: tu by bol rozcestnik vyplnanie / statistiky / mozno nieco dalsie
        // kedze zatial mame iba vyplnanie, proste dame redirect
        // TODO: toto chceme aby rovno redirectovalo na prvu ne-100% sekciu
        return new RedirectResponse($this->generateUrl('answer'));
    }

    /**
     *
     * @param EntityManager $em
     * @param User $user current user
     * @return array menu array of categories and their subcategories
     */
    private function buildMenu($em, $user) {
        $menu = array(
            'subject' => new MenuItem(
                'Predmety',
                $this->generateUrl('answer_subject')
            ),
            'general' => new MenuItem(
                'Všeobecné otázky',
                $this->generateUrl('answer_general')
            )
        );

        $subcategories = $em->getRepository('AnketaBundle\Entity\Category')
                       ->getOrderedGeneral();
        foreach ($subcategories as $subcategory) {
            $menu['general']->children[$subcategory->getId()] =
                new MenuItem(
                    $subcategory->getType(),
                    $this->generateUrl('answer_general', array('id' => $subcategory->getId()))
                    );
        }
        $subjects = $em->getRepository('AnketaBundle\Entity\Subject')
                       ->getAttendedSubjectForUser($user->getId());
        foreach($subjects as $subject) {
            $menu['subject']->children[$subject->getCode()] =
                new MenuItem(
                $subject->getName(),
                $this->generateUrl('answer_subject', array('code' => $subject->getCode()))
                );
        }

        $generalProgress = $em->getRepository('AnketaBundle\Entity\Question')
                       ->getGeneralProgress($user);
        foreach ($generalProgress AS $id => $data) {
            if (array_key_exists($id, $menu['general']->children)) {
                $menu['general']->children[$id]->setProgress((int) $data['answers'], 
                                                             (int) $data['questions']);
            }
            if ($data['category'] == 'subject') {
                $questionsPerSubject = $data['questions'];
            }
        }


        $subjectProgress = $em->getRepository('AnketaBundle\Entity\Question')
                       ->getSubjectProgress($user);
        foreach ($subjectProgress AS $id => $data) {
            if (array_key_exists($id, $menu['subject']->children)) {
                $menu['subject']->children[$id]->setProgress((int) $data['answers'],
                                                             (int) $questionsPerSubject);
            }
        }

        unset($menu['studijnyprogram']);

        return $menu;
    }

    public function userBarAction() {
        $user = $this->get('security.context')->getToken()->getUser();
        $params = array();
        $params['username'] = $user->getUserName();
        $params['displayname'] = $user->getDisplayName();
        return $this->render('AnketaBundle:Hlasovanie:user_bar.html.twig',
                             $params);
    }

    public function menuAction($activeItems) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $templateParams = array('menu' => $this->buildMenu($em, $user));

        $firstItem = array_shift($activeItems);
        $templateParams['menu'][$firstItem]->active = true;
        
        $current = &$templateParams['menu'][$firstItem]->children;
        foreach ($activeItems as $item) {
            $current[$item]->active = true;
            $current = &$current[$item]->children;
        }

        return $this->render('AnketaBundle:Hlasovanie:menu.html.twig',
                             $templateParams);
    }

}
