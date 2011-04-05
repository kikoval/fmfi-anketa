<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

use AnketaBundle\Entity\Question;
use AnketaBundle\Form\AddQuestionForm;

class ManageQuestionsController extends Controller
{
    public function listQuestionsAction()
    {
       $em = $this->get('doctrine.orm.entity_manager');
       $questions = $em->getRepository('AnketaBundle:Question')->findAll();
        return $this->render('AnketaBundle:ManageQuestions:listQuestions.html.twig',
                              array('questions' => $questions));
    }

    public function addQuestionAction()
    {
        $question = new Question();
        $form = AddQuestionForm::create($this->get('form.context'), 'add');

        // If a POST request, write the submitted data into $event
        // and validate the object
        $form->bind($this->get('request'), $question);

        // If the form has been submitted and is valid then save the event
        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            //$question.setOptions(str_replace("\n","#",$question.getOptions()));
            $em->persist($question);
            $em->flush();
            // $this->get('session')->setFlash('notice', 'Udalosť pridaná.');
            return new RedirectResponse($this->generateUrl('_viewquestion',
                           array('id' => $question->getId())));
        }

        // Display the form with the values in $event
        return $this->render('AnketaBundle:ManageQuestions:addQuestion.html.twig', array(
            'form' => $form,
        ));
    }

    public function viewQuestionAction($id)
    {
        // todo: parse options column and pass it to template as array
        $em = $this->get('doctrine.orm.entity_manager');
        $question = $em->find('AnketaBundle:Question', $id);
        return $this->render('AnketaBundle:ManageQuestions:viewQuestion.html.twig',
                              array('question' => $question));
    }
}
