<?php

namespace AnketaBundle\Menu;

use Symfony\Component\DependencyInjection\ContainerInterface;

class HlasovanieMenu
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    private function generateUrl($route, $parameters = array(), $absolute = false) {
        return $this->container->get('router')->generate($route, $parameters, $absolute);
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

    public function render($activeItems = array()) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->container->get('doctrine.orm.entity_manager');
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

        return $this->container->get('templating')->render('AnketaBundle::menu.html.twig',
                                                           $templateParams);
    }

    public function getNextSection($activeItems = array()) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->container->get('doctrine.orm.entity_manager');
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
            return $current[$myChildren[0]]->href;
        }

        for ($i = count($nextSibling) - 1; $i >= 0; $i--) {
            if($nextSibling[$i] !== null) {
                return $nextSibling[$i]->href;
            }
        }

        return null;
    }

}
