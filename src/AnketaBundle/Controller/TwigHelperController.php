<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Controller
 * @author     Jakub Markoš <jakub.markos@gmail.com>
 */

/**
 * Controller for answering questions
 */

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

use AnketaBundle\Entity\Answer;
use AnketaBundle\Entity\User;

class TwigHelperController extends Controller {

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
        return $this->render('AnketaBundle:TwigHelper:user_bar.html.twig',
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

        return $this->render('AnketaBundle:TwigHelper:menu.html.twig',
                             $templateParams);
    }

}
