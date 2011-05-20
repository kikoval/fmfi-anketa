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
use AnketaBundle\Entity\Category;
use AnketaBundle\Entity\CategoryType;

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

    /**
     * Note: code may be "-1" meaning default first subject
     */
    public function getAttendedSubjectByCode($user, $code) {
        $em = $this->get('doctrine.orm.entity_manager');
        $attendedSubjects = $em->getRepository('AnketaBundle\Entity\Subject')
                               ->getAttendedSubjectForUser($user->getId());

        if (count($attendedSubjects) == 0) {
            throw new \RuntimeException ('Nemas ziadne predmety.');
        }

        // defaultne vraciame abecedne prvy predmet
        if ($code == -1) {
            $subject = $attendedSubjects[0];
        } else {
            $subject = $em->getRepository('AnketaBundle\Entity\Subject')
                          ->findOneBy(array('code' => $code));
            if (empty($subject)) {
                throw new \RuntimeException('Chybny kod: ' . $code);
            }
            if (!in_array($subject, $attendedSubjects)) {
                throw new \RuntimeException('Predmet ' . $code . ' nemas zapisany');
            }
        }

        return $subject;
    }

    public function answerSubjectTeacherAction($subject_code, $teacher_code) {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        try {
            $subject = $this->getAttendedSubjectByCode($user, $subject_code);
        } catch (\RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                        ->getOrderedQuestionsByCategoryType(CategoryType::TEACHER_SUBJECT);
        // TODO: by Season
        $teachers = $subject->getTeachers();
        $teacher = null;
        foreach ($teachers as $tmp) {
            if ($tmp->getId() == $teacher_code) $teacher = $tmp;
        }
        if ($teacher == null) {
            throw new NotFoundHttpException("Ucitel " . $teacher_code . " neuci dany predmet");
        }

        $answers = $em->getRepository('AnketaBundle\Entity\Answer')
                      ->getAnswersByCriteria($questions, $user, $subject, $teacher);

        if ('POST' == $request->getMethod()) {
            $answerArray = $this->processForm($request, $user, $questions, $answers);

            foreach ($answerArray AS $answer) {
                // chceme nastavit este teacher + subject
                // predpokladame ze subject je to co prislo v parametri kodu
                $answer->setSubject($subject);
                // ako ucitela zatial zoberieme prveho... co asi urcite nechceme
                $answer->setTeacher($teacher);
                $answer->setAttended(true);

                $em->persist($answer);
            }

            $em->flush();

            if ($request->request->get('next')) {
                return $this->forward('AnketaBundle:Hlasovanie:menuNext',
                    array('activeItems' => array('subject', $subject->getCode(), $teacher->getId())));
            }
            else {
                return new RedirectResponse($request->getRequestUri());
            }
        }

        $templateParams = array();
        $templateParams['title'] = $subject->getName() . ' - ' . $teacher->getName();
        $templateParams['activeItems'] = array('subject', $subject->getCode(), $teacher->getId());
        $templateParams['questions'] = $questions;
        $templateParams['answers'] = $answers;
        $templateParams['categoryType'] = 'teacher_subject';
        return $this->render('AnketaBundle:Question:index.html.twig', $templateParams);

    }

    public function answerSubjectAction($code) {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        try {
            $subject = $this->getAttendedSubjectByCode($user, $code);
        } catch (\RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                        ->getOrderedQuestionsByCategoryType(CategoryType::SUBJECT);
        $answers = $em->getRepository('AnketaBundle\Entity\Answer')
                      ->getAnswersByCriteria($questions, $user, $subject);

        if ('POST' == $request->getMethod()) {
            $answerArray = $this->processForm($request, $user, $questions, $answers);

            foreach ($answerArray AS $answer) {
                // chceme nastavit este teacher + subject
                // predpokladame ze subject je to co prislo v parametri kodu
                $answer->setSubject($subject);
                $answer->setTeacher(null);
                // aktualne sa daju vyplnat iba predmety ktore sme navstevovali 
                $answer->setAttended(true);

                $em->persist($answer);
            }

            $em->flush();

            if ($request->request->get('next')) {
                return $this->forward('AnketaBundle:Hlasovanie:menuNext',
                    array('activeItems' => array('subject', $subject->getCode())));
            }
            else {
                return new RedirectResponse($request->getRequestUri());
            }
        }

        $templateParams = array();
        $templateParams['title'] = $subject->getName();
        $templateParams['activeItems'] = array('subject', $subject->getCode());
        $templateParams['questions'] = $questions;
        $templateParams['answers'] = $answers;
        $templateParams['categoryType'] = 'subject';
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
        $request = $this->get('request');
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

            if (empty($category) ||
                ($category->getType() !== CategoryType::GENERAL)) {
                throw new NotFoundHttpException ('Chybna kategoria: ' . $id);
            }
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

            if ($request->request->get('next')) {
                return $this->forward('AnketaBundle:Hlasovanie:menuNext',
                    array('activeItems' => array('general', $category->getId())));
            }
            else {
                return new RedirectResponse($request->getRequestUri());
            }
        }

        $templateParams = array();
        $templateParams['title'] = $category->getDescription();
        $templateParams['activeItems'] = array('general', $category->getId());
        $templateParams['questions'] = $questions;
        $templateParams['answers'] = $answers;
        $templateParams['categoryType'] = 'general';
        return $this->render('AnketaBundle:Question:index.html.twig', $templateParams);
    }

    public function answerIncompleteAction() {
        //TODO(majak): spravit redirect na prvu sekciu,
        //             ktora nema 100% vyplnenie
        return new RedirectResponse($this->generateUrl('answer'));
    }
}
