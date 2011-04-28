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

use AnketaBundle\Entity\Answer;

class AnswerController extends Controller {

    public function indexAction() {
        return $this->render('AnketaBundle:Answer:index.html.twig');
    }

    /**
     * Processes the form.
     * 
     * @param Request $request
     * @param User $user current user
     * @return array array of partially-created answers (without
     * subject, category, user, etc fields set)
     */
    private function processForm($request, $user) {

        $em = $this->get('doctrine.orm.entity_manager');

        $questionArray = $request->request->get('question');

        $result = array();
        // prechadzame otazky prvky
        foreach ($questionArray as $questionId => $question) {
            // todo: validacia oboch id-ciek
            // todo: validacia ci uz neodpovedal - ak ano, treba iba updatovat
            // todo: milion dalsich validacii - ma ten predmet / studijny
            //       program vobec zapisany atd

            // ziskame question+option z db
            $questionObj = $em->find('AnketaBundle:Question', $questionId);
            $option = $em->find('AnketaBundle:Option', $question['answer']);

            $comment = $question['comment'];

            $answer = new Answer();
            $answer->setQuestion($questionObj);
            // evaluacia sa nastavi z Option
            $answer->setOption($option);
            $answer->setAuthor($user);
            $answer->setComment($comment);

            $result[] = $answer;
        };
        
        return $result;
    }

    public function answerSubjectAction($code) {
        if ($code == -1) {
            // default predmet
            $code = 'met001';
        }

        $request = Request::createFromGlobals();
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $attendedSubjects = $user->getSubjects();
        // ak sa tuto nic nenajde, tak by to chcelo redirect na nejaku error page
        $subject = $em->getRepository('AnketaBundle\Entity\Subject')
                      ->findOneBy(array('code' => $code));

        if ('POST' == $request->getMethod()) {
            $answerArray = $this->processForm($request, $user);
            // neviem presne ako funguje ta doctrine ArrayCollection, ci
            // sa tam da nejako vyhladavat alebo co, takze to robim takto
            $attended = false;
            foreach ($attendedSubjects AS $item) {
                if ($item->getCode() == $subject->getCode())
                    $attended = true;
            }
            foreach ($answerArray AS $answer) {
                // chceme nastavit teacher + subject
                // predpokladame ze subject je to co prislo v parametri kodu
                $answer->setSubject($subject);
                // ako ucitela zoberieme prveho... co asi urcite nechceme
                $answer->setTeacher($subject->getTeachers()->get(0));
                $answer->setAttended($attended);

                $em->persist($answer);
            }

            $em->flush();
        }
        $category = $em->getRepository('AnketaBundle\Entity\Category')
	               ->findOneBy(array('category' => 'subject'));
        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                        ->findBy(array('category' => $category->getId()));
        $username = $user->getUserName();

        return $this->render('AnketaBundle:Answer:answerSubject.html.twig',
                array('questions' => $questions, 'username' => $username,
                      'attendedsubjects' => $attendedSubjects, 'subject' => $subject));
    }

    public function answerGeneralAction($id) {
        // nejaka default kategoria
        if ($id == -1) {
            // zatial 1, v realnej app bude treba ziskat id nejakej default kat.
            $id = 1;
        }

        $request = Request::createFromGlobals();
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');

        if ('POST' == $request->getMethod()) {
            $answerArray = $this->processForm($request, $user);
            foreach ($answerArray AS $answer) {
                $em->persist($answer);
            }

            $em->flush();
            // redirect na stranku s dalsimi otazkami
        } else {
            // ak neni POST request, tak chceme mozno zobrazit jeho
            // predchadzajuce odpovede, pripadne odpovede/komentare ostatnych ludi
        }

        // chceme vceobecne subkategorie - pre menu do templatu
        $subcategories = $em->getRepository('AnketaBundle\Entity\Category')
                       ->findBy(array('category' => 'general'));

        // a kategoriu podla parametru (posiela sa aj do templatu)
        $category = $em->find('AnketaBundle:Category', $id);
        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                        ->findBy(array('category' => $category->getId()));
        $username = $user->getUserName();

        return $this->render('AnketaBundle:Answer:answerGeneral.html.twig',
                array('questions' => $questions, 'username' => $username,
                      'subcategories' => $subcategories, 'category' => $category));
    }
}
