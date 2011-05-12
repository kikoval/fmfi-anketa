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

class QuestionController extends Controller {

    /**
     * Processes the form.
     * 
     * @param Request $request
     * @param User $user current user
     * @param ArrayCollection $questions questions which are expected in the form
     * @param ArrayCollection $answers answers already filled before
     * @return array array of updated or created answers
     */
    private function processForm($request, $user, $questions, $answers) {

        $em = $this->get('doctrine.orm.entity_manager');

        $questionArray = $request->request->get('question');

        $result = array();
        // prechadzame otazky na ktore sa ocakava mozna odpoved
        foreach ($questions as $question) {
            // ak vobec vyplnil otazku - tzn vybral nejaku moznost (a nejake existovali)
            // a/alebo vyplnil komentar (a otazka komentar mala)
            $optionFilled = isset($questionArray[$question->getId()]['answer']);
            $id = $question->getId();
            if (!array_key_exists($id, $questionArray)) {
                // TODO(ppershing): throw an exception here?
                continue;
            }

            // Warning: do not use array_key_exists, $answers[$id] may be NULL
            if (isset($answers[$id])) {
                $answer = $answers[$id];
            } else {
                $answer = new Answer();
                $answer->setQuestion($question);
                $answer->setAuthor($user);
            }

            if (isset($questionArray[$id]['answer'])) {
                $optionId = $questionArray[$id]['answer'];
                if ($optionId == -1) {
                    $option = null;
                } else {
                    $option = $em->find('AnketaBundle:Option', $optionId);
                    if (!($question->getOptions()->contains($option))) {
                        // TODO(ppershing): throw an exception
                        continue;
                    }
                }

                $answer->setOption($option);
            } else {
                $answer->setOption(null);
            }


            if (isset($questionArray[$id]['comment']) &&
                trim($questionArray[$id]['comment']) != '') {
                $answer->setComment(trim($questionArray[$id]['comment']));
            } else {
                $answer->setComment(null);
            }

            $result[] = $answer;
        }
        return $result;
    }

    public function answerSubjectAction($code) {
        $request = Request::createFromGlobals();
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $attendedSubjects = $em->getRepository('AnketaBundle\Entity\Subject')
                               ->getAttendedSubjectForUser($user->getId());

        if (count($attendedSubjects) == 0)
            throw new NotFoundHttpException ('Nemas ziadne predmety.');

        // defaultne vraciame abecedne prvy predmet
        if ($code == -1) {
            $subject = $attendedSubjects[0];
        } else {
            $subject = $em->getRepository('AnketaBundle\Entity\Subject')
                          ->findOneBy(array('code' => $code));
            if (empty($subject))
                throw new NotFoundHttpException ('Chybny kod: ' . $code);
        }

        $category = $em->getRepository('AnketaBundle\Entity\Category')
	               ->findOneBy(array('category' => 'subject'));
        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                        ->getOrderedQuestions($category);
        $answers = $em->getRepository('AnketaBundle\Entity\Answer')
                      ->getAnswersByCriteria($questions, $user, $subject);

        $attended = false;
        $key = \array_search($subject, $attendedSubjects);
        if ($key !== false) {
            $attended = true;
        }
        if ('POST' == $request->getMethod()) {
            $answerArray = $this->processForm($request, $user, $questions, $answers);

            foreach ($answerArray AS $answer) {
                // chceme nastavit este teacher + subject
                // predpokladame ze subject je to co prislo v parametri kodu
                $answer->setSubject($subject);
                // ako ucitela zatial zoberieme prveho... co asi urcite nechceme
                $answer->setTeacher($subject->getTeachers()->get(0));
                $answer->setAttended($attended);

                $em->persist($answer);
            }

            $em->flush();

            // redirect na stranku s dalsimi otazkami
            if (($key !== false) && ($key < (count($attendedSubjects) - 1)))
                return new RedirectResponse($this->generateUrl('answer_subject',
                                    array('code' => $attendedSubjects[$key + 1]->getCode())));
            return new RedirectResponse($this->generateUrl('answer'));
        }

        $templateParams = array();
        $templateParams['title'] = $subject->getName();
        $templateParams['activeItems'] = array('subject', $subject->getCode());
        $templateParams['questions'] = $questions;
        $templateParams['answers'] = $answers;
        return $this->render('AnketaBundle:Question:index.html.twig', $templateParams);
    }

    public function answerGeneralAction($id) {
        /**
         * Co sa tu robi?
         *   - spracovanie parametru
         *   - ziskat potrebne otazky
         *   - ziskat odpovede, ak existuju, a poslat ich tiez do templatu
         * Ak je POST request, tak treba naviac
         *   - spracovanie formovych dat (fcia processForm)
         *   - updatovanie / vytvorenie odpovedi (fcia processForm)
         *   - persistovanie odpovedi
         */
        $request = Request::createFromGlobals();
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        
        // chceme vceobecne subkategorie - pre menu do templatu
        // TODO toto je code duplication s buildMenu, tuto informaciu aj tak dostaneme v templateParams
        $subcategories = $em->getRepository('AnketaBundle\Entity\Category')
                       ->getOrderedGeneral();
        if (empty($subcategories))
            throw new NotFoundHttpException ('Ziadne vseobecne kategorie.');

        // default prva kategoria (najnizsie position)
        if ($id == -1) {
            $category = $subcategories[0];
        } else {
            // kontrola na integer sa odohrala uz v routovani
            $category = $em->find('AnketaBundle:Category', $id);
            if (empty($category) || ($category->getCategory() !== 'general'))
                throw new NotFoundHttpException ('Chybna kategoria: ' . $id);
        }
        
        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                        ->getOrderedQuestions($category);
        $answers = $em->getRepository('AnketaBundle\Entity\Answer')
                      ->getAnswersByCriteria($questions, $user);

        if ('POST' == $request->getMethod()) {
            $answerArray = $this->processForm($request, $user, $questions, $answers);
            foreach ($answerArray AS $answer) {
                $em->persist($answer);
            }

            $em->flush();

            // redirect na stranku s dalsimi otazkami - tzn na dalsiu
            // subkategoriu v abecednom poradi
            // TODO toto chceme spojit s answerSubjectAction a v oboch pripadoch proste pouzit info z buildMenu
            // TODO a tiez chceme podporovat aj "uloz" button co redirectuje na aktualnu stranku
            $key = \array_search($category, $subcategories);
            if ($key === false)
                throw new \Exception('Something went wrong!');
            if ($key < (count($subcategories) - 1))
                return new RedirectResponse($this->generateUrl('answer_general',
                                array('id' => $subcategories[$key + 1]->getId())));
            return new RedirectResponse($this->generateUrl('answer_subject'));
        }

        $templateParams = array();
        $templateParams['title'] = $category->getType();
        $templateParams['activeItems'] = array('general', $category->getId());
        $templateParams['questions'] = $questions;
        $templateParams['answers'] = $answers;
        return $this->render('AnketaBundle:Question:index.html.twig', $templateParams);
    }
}
