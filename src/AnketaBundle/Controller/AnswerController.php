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

class AnswerController extends Controller {

    public function indexAction() {
        return $this->render('AnketaBundle:Answer:index.html.twig');
    }

    public function answerGeneralAction() {
        $request = Request::createFromGlobals();

        $em = $this->get('doctrine.orm.entity_manager');

        if ('POST' == $request->getMethod()) {

//            evaluation
//            comment
//            subject_id
//            question_id
//            option_id
//            teacher_id
//            author_id

            foreach ($request->request->all() as $question => $option) {
                // validacia oboch id-ciek

            };

            // tuto by sme radi mozno nieco spravili s odpovedami - coming soon

            // redirect na stranku s dalsimi otazkami
        }

        $category = $em->getRepository('AnketaBundle\Entity\Category')
                       ->findOneBy(array('category' => 'general'));

        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                        ->findBy(array('category' => $category->getId()));

        $user = $this->get('security.context')->getToken()->getUser();
        $username = $user->getUsername();

        return $this->render('AnketaBundle:Answer:answerGeneral.html.twig',
                array('questions' => $questions, 'username' => $username));

    }
}
