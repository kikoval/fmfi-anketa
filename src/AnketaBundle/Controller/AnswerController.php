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

    public function answerGeneralAction() {
        $request = Request::createFromGlobals();

        $em = $this->get('doctrine.orm.entity_manager');

        if ('POST' == $request->getMethod()) {
            $user = $this->get('security.context')->getToken()->getUser();

            $questionArray = $request->request->get('question');

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

                // tu vznika otazka, ako ziskat id predmetu / ucitela? ako nejaky
                // hidden field vo forme? zatial null

                $answer = new Answer();
                $answer->setQuestion($questionObj);
                // evaluacia sa nastavi z Option
                $answer->setOption($option);
                $answer->setAuthor($user);
                $answer->setComment($comment);

                $em->persist($answer);
            };

            $em->flush();

            // redirect na stranku s dalsimi otazkami
        } else {
            // ak neni POST request, tak chceme mozno zobrazit jeho
            // predchadzajuce odpovede, pripadne odpovede/komentare ostatnych ludi
        }

        // chceme vceobecne otazky
        $category = $em->getRepository('AnketaBundle\Entity\Category')
                       ->findOneBy(array('category' => 'general'));

        // najdeme tieto vseobecne otazky - chcelo by to asi viac vseobecnych
        // kategorii aby nebolo 100 otazok na jednej stranke
        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                        ->findBy(array('category' => $category->getId()));

        $user = $this->get('security.context')->getToken()->getUser();
        $username = $user->getUsername();

        /**
         * ak by sme chceli mat iba jednu controller akciu na vsetky typy
         * listovania otazok, tak bude treba do templatu poslat nieco ako
         * nadpis (o aky typ otazok sa jedna), predmet (co za predmet),
         * ucitel (kto to uci) apod
         */
        return $this->render('AnketaBundle:Answer:answerGeneral.html.twig',
                array('questions' => $questions, 'username' => $username));

    }
}
