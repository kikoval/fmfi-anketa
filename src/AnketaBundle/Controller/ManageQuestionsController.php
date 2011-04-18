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

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\Option;
use AnketaBundle\Entity\Category;
use AnketaBundle\Form\AddQuestionForm;

/**
 * Controller for managing questions - adding, viewing, deleting, editing
 */

class ManageQuestionsController extends Controller {

    public function listQuestionsAction() {
       $em = $this->get('doctrine.orm.entity_manager');
       $questions = $em->getRepository('AnketaBundle:Question')->findAll();
        return $this->render('AnketaBundle:ManageQuestions:listQuestions.html.twig',
                              array('questions' => $questions));
    }

    public function addQuestionAction() {
        $question = new Question();

        $request = Request::createFromGlobals();

        if ('POST' == $request->getMethod()) {
            // asi bude treba nejake validacie vstupu - na druhej strane,
            // snad nebude moct hocikto pridavat otazky

            $em = $this->get('doctrine.orm.entity_manager');

            $question->setQuestion($request->request->get('_question'));

            if ($request->request->get('_stars')) {
                $question->generateStarOptions();
            } else {
                // treba doriesit ako nastavit evaluacie, zatial vsetky 0
                $textAr = explode("\n", $request->request->get('_options'));
                foreach ($textAr as $option) {
                    $question->addOption(new Option($option, 0));
                }
            }

            // treba nejako spravit aby sa dali kategorie vyberat zo select boxu
            // zatial default general kategoria
            $category = $em->getRepository('AnketaBundle\Entity\Category')
                      ->findOneBy(array('category' => 'general'));
            $question->setCategory($category);

            $em->persist($question);
            $em->flush();
            return new RedirectResponse($this->generateUrl('_viewquestion',
                           array('id' => $question->getId())));
        }

        return $this->render('AnketaBundle:ManageQuestions:addQuestion.html.twig', array(
            'question' => $question,
        ));
    }

    public function viewQuestionAction($id) {
        $em = $this->get('doctrine.orm.entity_manager');
        $question = $em->find('AnketaBundle:Question', $id);
        // alternativny sposob:
//        $question = $em->getRepository('AnketaBundle\Entity\Question')->getQuestion($id);
        return $this->render('AnketaBundle:ManageQuestions:viewQuestion.html.twig',
                              array('question' => $question));        
    }

    public function answerQuestionsAction() {
        $em = $this->get('doctrine.orm.entity_manager');

        // tu bude treba vytiahnut nejaku rozumnu mnozinu otazok, napr otazky
        // tykajuce sa matalyzy, tykajuce sa skoly apod - potom premenovat akciu
        // na nieco specifickejsie, pripadne pridat argument

        // zatial vyberam vsetky
        $questions = $em->getRepository('AnketaBundle:Question')->findAll();
        
        

        return $this->render('AnketaBundle:ManageQuestions:answerQuestions.html.twig',
                array('questions' => $questions));
        
    }
}
