<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AnketaBundle\Entity\User;

class HlasovanieController extends Controller
{
    public function indexAction()
    {
        // TODO: toto chceme aby rovno redirectovalo na prvu ne-100% sekciu
        $security = $this->get('security.context');
        if ($security->isGranted('ROLE_HAS_VOTE')) {
            return new RedirectResponse($this->generateUrl('answer_incomplete'));
        }
        else if ($security->isGranted('ROLE_STUDENT')) {
            return $this->render('AnketaBundle:Hlasovanie:dakujeme.html.twig');
        }
        else {
            return $this->render('AnketaBundle:Hlasovanie:novote.html.twig');
        }
    }

    /**
     *
     * @param EntityManager $em
     * @param User $user current user
     * @return array menu array of categories and their subcategories
     */
    private function buildMenu($em, $user) {
        $season = $em->getRepository('AnketaBundle:Season')->getActiveSeason();

        $menu = array(
            'subject' => new MenuItem(
                'Predmety',
                $this->generateUrl('answer_subject')
            ),
            'study_program' => new MenuItem(
                'Študijné programy',
                $this->generateUrl('answer_study_program')
            ),
            'general' => new MenuItem(
                'Všeobecné otázky',
                $this->generateUrl('answer_general')
            ),
            'anonymizuj' => new MenuItem(
                    'Ukončenie hlasovania',
                    $this->generateUrl('anonymizuj')
            )
        );

        // pridame menu pre vseobecne otazky
        $subcategories = $em->getRepository('AnketaBundle\Entity\Category')
                       ->getOrderedGeneral();
        foreach ($subcategories as $subcategory) {
            $menu['general']->children[$subcategory->getId()] =
                new MenuItem(
                    $subcategory->getDescription(),
                    $this->generateUrl('answer_general', array('id' => $subcategory->getId()))
                    );
        }

        // pridame menu pre predmety
        $subjects = $em->getRepository('AnketaBundle\Entity\Subject')
                       ->getAttendedSubjectsForUser($user, $season);
        $teacherRepository = $em->getRepository('AnketaBundle:Teacher');
        foreach($subjects as $subject) {
            $subjectMenu = 
                new MenuItem(
                $subject->getName(),
                $this->generateUrl('answer_subject', array('code' => $subject->getCode()))
                );
            // TODO: optimalizovat selecty
            // pridame vnorene menu pre predmetoucitelov
            $teachers = $teacherRepository->getTeachersForSubject($subject, $season);
            foreach ($teachers as $teacher) {
                $subjectMenu->children[$teacher->getId()] =
                    new MenuItem(
                            $teacher->getName(),
                            $this->generateUrl('answer_subject_teacher',
                                array('subject_code' => $subject->getCode(),
                                      'teacher_code' => $teacher->getId()))
                            );

            }
            $menu['subject']->children[$subject->getCode()] = $subjectMenu;
        }

        // pridame menu pre studijne programy
        $studyProgrammes = $em->getRepository('AnketaBundle\Entity\StudyProgram')
                       ->getStudyProgrammesForUser($user, $season);
        foreach ($studyProgrammes as $studyProgramme) {
            //print_r($studyProgramme->getCode());
            $menu['study_program']->children[$studyProgramme->getCode()] =
                new MenuItem(
                    $studyProgramme->getName().' ('.$studyProgramme->getCode().')',
                    $this->generateUrl('answer_study_program', array('slug' => $studyProgramme->getSlug()))
                    );
        }
        

        // nastavime progress
        $questionRepository = $em->getRepository('AnketaBundle\Entity\Question');
        
        foreach ($questionRepository->getProgressForSubjectTeachersByUser($user, $season) as $subject => $rest) {
            foreach ($rest as $teacher => $progress) {
                
                $menu['subject']->children[$subject]
                                ->children[$teacher]
                                ->getProgressbar()
                                ->setProgress((int)$progress['answered'],
                                              (int)$progress['total']);
            }
        }

        foreach ($questionRepository->getProgressForSubjectsByUser($user, $season) as $subject => $progress) {
            $menu['subject']->children[$subject]
                            ->getProgressbar()
                            ->setProgress((int)$progress['answered'],
                                          (int)$progress['total']);
            $menu['subject']->children[$subject]
                            ->getProgressbar()
                            ->setIncludeChildren(false);
        }

        foreach ($questionRepository->getProgressForCategoriesByUser($user, $season) as $categoryId => $progress) {
            if (array_key_exists($categoryId, $menu['general']->children)) {
                $menu['general']->children[$categoryId]
                                ->getProgressbar()
                                ->setProgress((int)$progress['answered'],
                                              (int)$progress['total']);
            }
        }

        foreach ($questionRepository->getProgressForStudyProgramsByUser($user, $season) as $studyProgramId => $progress) {
            if (array_key_exists($studyProgramId, $menu['study_program']->children)) {
                $menu['study_program']->children[$studyProgramId]
                                ->getProgressbar()
                                ->setProgress((int)$progress['answered'],
                                              (int)$progress['total']);
            }
        }

        return $menu;
    }

    public function menuAction($activeItems = array()) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $templateParams = array('menu' => $this->buildMenu($em, $user));

        $activeTail = null;
        $current = &$templateParams['menu'];
        foreach ($activeItems as $item) {
            $activeTail = $current[$item];
            $current[$item]->expanded = true;
            $current = &$current[$item]->children;
        }
        if ($activeTail) {
            $activeTail->active = true;
        }

        return $this->render('AnketaBundle:Hlasovanie:menu.html.twig',
                             $templateParams);
    }

    public function menuNextAction($activeItems = array()) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $menu = $this->buildMenu($em, $user);

        // pre kazdu aktivnu polozku spocitame jej praveho surodenca.
        $nextSibling = array();
        $current = &$menu;
        foreach ($activeItems as $item) {
            $siblings = array_keys($current);
            $myIndex = array_search($item, $siblings);
            $nextSibling[] = ($myIndex === false || $myIndex + 1 == count($siblings) ? null : $current[$siblings[$myIndex + 1]]);

            $current = &$current[$item]->children;
        }

        $myChildren = array_keys($current);
        if (!empty($myChildren)) {
            return new RedirectResponse($current[$myChildren[0]]->href);
        }

        for ($i = count($nextSibling) - 1; $i >= 0; $i--) {
            if($nextSibling[$i] !== null) {
                return new RedirectResponse($nextSibling[$i]->href);
            }
        }

        return new RedirectResponse($this->get('request')->getRequestUri());
    }

    public function globalProgressbarAction($mode) {
        $em = $this->get('doctrine.orm.entity_manager');

        $activeSeason = $em->getRepository('AnketaBundle\Entity\Season')->getActiveSeason();
        $total = $activeSeason->getStudentCount();
        $voters = $em->getRepository('AnketaBundle\Entity\User')
                     ->getNumberOfVoters($activeSeason);
        $anon = $em->getRepository('AnketaBundle\Entity\User')
                   ->getNumberOfAnonymizations($activeSeason);

        $templateParams = array();
        $templateParams['progressAnon'] = new MenuItemProgressbar(null, $total, $anon);
        $templateParams['progressVoters'] = new MenuItemProgressbar(null, $total, $voters);
        $templateParams['voters'] = $voters;
        $templateParams['anon'] = $anon;
        $templateParams['mode'] = $mode;
        return $this->render('AnketaBundle:Hlasovanie:globalProgressbar.html.twig',
                             $templateParams);
    }

}
