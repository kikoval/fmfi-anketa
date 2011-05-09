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

class AnswerController extends Controller {

    /**
     * Compares 2 subjects based on their name property
     */
    private static function compareSubjects($a, $b) {
        if ($a == $b) {
            return 0;
        }
        return \strcmp($a->getName(), $b->getName());
    }

    /**
     *
     * @param EntityManager $em
     * @param User $user current user
     * @return array menu array of categories and their subcategories
     */
    private function buildMenu($em, $user) {
        $menu = array(
            'subject' => array(
                'title' => 'Predmety',
                'href' => $this->generateUrl('answer_subject'),
                'children' => array(),
                'active' => false
            ),
            'general' => array(
                'title' => 'Všeobecné otázky',
                'href' => $this->generateUrl('answer_general'),
                'children' => array(),
                'active' => false
            )
        );

        $subcategories = $em->getRepository('AnketaBundle\Entity\Category')
                       ->getOrderedGeneral();
        foreach ($subcategories as $subcategory) {
            $menu['general']['children'][$subcategory->getId()] = array(
                'title' => $subcategory->getType(),
                'href' => $this->generateUrl('answer_general', array('id' => $subcategory->getId())),
                'active' => false
            );
        }

        $attendedSubjects = $user->getSubjects()->toArray();
        \usort($attendedSubjects, array($this, 'compareSubjects'));
        foreach($attendedSubjects as $subject) {
            $menu['subject']['children'][$subject->getCode()] = array(
                'title' => $subject->getName(),
                'href' => $this->generateUrl('answer_subject', array('code' => $subject->getCode())),
                'active' => false
            );
        }

        return $menu;
    }

    /**
     *
     * @param EntityManager $em
     * @param User $user current user
     * @return array array of parameters for template
     */
    private function getCommonData($em, $user) {
        $result = array();

        $result['username'] = $user->getUserName();

        $result['displayname'] = $user->getDisplayName();

        $menu = $this->buildMenu($em, $user);

        $generalProgress = $em->getRepository('AnketaBundle\Entity\Question')
                       ->getGeneralProgress($user);
        $numQuestions = array('general' => 0, 'subject' => 0, 'studijnyprogram' => 0);
        $numAnswers = array('general' => 0, 'subject' => 0, 'studijnyprogram' => 0);
        foreach ($generalProgress AS $id => $data) {
            $numQuestions[$data['category']] += $data['questions'];
            $numAnswers[$data['category']] += $data['answers'];
            if ($data['category'] == 'general' && isset($menu['general']['children'][$id]) && $data['questions'] != 0) {
                $menu['general']['children'][$id]['progress'] = round(100 * $data['answers'] / $data['questions']);
            }
        }

        $subjectProgress = $em->getRepository('AnketaBundle\Entity\Question')
                       ->getSubjectProgress($user);
        foreach ($subjectProgress AS $id => $data) {
            if (isset($menu['subject']['children'][$id]) && $numQuestions['subject'] != 0) {
                $menu['subject']['children'][$id]['progress'] = round(100 * $data['answers'] / $numQuestions['subject']);
            }
        }

        // od filledQuestions treba este odpocitat odpovede z nezapisanych predmetov
        // TODO: huh? mozes vobec vyplnit odpoved z predmetu co nemas zapisany?
        $attendedCodes = array();
        foreach ($user->getSubjects() AS $subject) {
            $attendedCodes[] = $subject->getCode();
        }
        foreach ($subjectProgress AS $code => $data) {
            if (\array_search($code, $attendedCodes) === false)
                $numAnswers['subject'] -= $data['answers'];
        }

        $numQuestions['subject'] *= $user->getSubjectsCount();

        $allQuestions = 0;
        $allAnswers = 0;
        foreach($numQuestions as $category => $value) {
            if ($value != 0) {
                $menu[$category]['progress'] = round(100 * $numAnswers[$category] / $value);
            }
            $allQuestions += $value;
            $allAnswers += $numAnswers[$category];
        }

        if ($allAnswers == $allQuestions) {
            $result['progress'] = 'Ďakujeme, vyplnil si všetky relevantné otázky.';
        } else {
            $result['progress'] = 'Zatiaľ si vyplnil ' . $allAnswers . '/' . $allQuestions . ' otázok.';
        }

        unset($menu['studijnyprogram']);

        $result['menu'] = $menu;

        return $result;
    }

    public function indexAction() {
        return $this->render('AnketaBundle:Answer:index.html.twig');
    }

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
            if (($optionFilled && $question->hasOptions()) ||
                (!empty($questionArray[$question->getId()]['comment']) && $question->getHasComment())) {
                // ak uz odpovedal niekedy, tak iba updatneme
                if (isset($answers[$question->getId()])) {
                    $answer = $answers[$question->getId()];
                } else {
                    // inak vyrobime novu odpoved
                    $answer = new Answer();
                }
                if ($optionFilled) {
                    $optionId = $questionArray[$question->getId()]['answer'];
                    $option = $em->find('AnketaBundle:Option', $optionId);
                    // evaluacia sa nastavi z Option
                    $answer->setOption($option);
                }
                
                $comment = $questionArray[$question->getId()]['comment'];
                $answer->setQuestion($question);
                $answer->setAuthor($user);
                $answer->setComment($comment);

                $result[] = $answer;
            }
        }
        return $result;
    }

    public function answerSubjectAction($code) {
        $request = Request::createFromGlobals();
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        // TODO toto je code duplication s buildMenu, tuto informaciu aj tak dostaneme v templateParams
        $attendedSubjects = $user->getSubjects()->toArray();
        \usort($attendedSubjects, array($this, 'compareSubjects'));

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

        $templateParams = $this->getCommonData($em, $user);
        $templateParams['menu']['subject']['active'] = true;
        $templateParams['menu']['subject']['children'][$subject->getCode()]['active'] = true;
        $templateParams['category'] = $templateParams['menu']['subject']['children'][$subject->getCode()];
        $templateParams['questions'] = $questions;
        $templateParams['answers'] = $answers;
        return $this->render('AnketaBundle:Answer:index.html.twig', $templateParams);
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

        $templateParams = $this->getCommonData($em, $user);
        $templateParams['menu']['general']['active'] = true;
        $templateParams['menu']['general']['children'][$category->getId()]['active'] = true;
        $templateParams['category'] = $templateParams['menu']['general']['children'][$category->getId()];
        $templateParams['questions'] = $questions;
        $templateParams['answers'] = $answers;
        return $this->render('AnketaBundle:Answer:index.html.twig', $templateParams);
    }
}
