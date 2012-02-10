<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AnketaBundle\Entity\Question;
use AnketaBundle\Entity\CategoryType;
use AnketaBundle\Entity\Season;
use AnketaBundle\Entity\Subject;
use DateTime;
use AnketaBundle\Lib\StatisticalFunctions;

class StatisticsController extends Controller {
    const MIN_VOTERS_FOR_PUBLIC = 0;
    const INTERVAL_CONFIDENCE = 0.9;
    const NO_CATEGORY = 'XXX-nekategorizovane';

    /**
     * @param string $season_slug if null, returns current active season
     * @returns Season the season
     */
    private function getSeason($season_slug = null) {
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('AnketaBundle\Entity\Season');
        if ($season_slug === null) {
            $season = $repository->getActiveSeason();
        } else {
            $season = $repository->findOneBy(array('slug' => $season_slug));
        }
        if ($season == null) {
            throw new NotFoundHttpException('Chybna sezona: ' . $season_slug);
        }
        return $season;
    }

    /**
     * Returns all comments from the set of answers
     */
    public function getComments($answers) {
        $comments = array();

        foreach ($answers as $answer) {
            if ($answer->hasComment() && !$answer->getInappropriate()) {
                $comments[] = array('answer_id' => $answer->getId(),
                                    'comment' => $answer->getComment());
            }
        }

        return $comments;
    }

    /**
     * @returns array(array('cnt'=>int,'title'=>string,'value'=>double))
     */
    public function getHistogramData($question, $answers) {
        if (!$question->hasOptions()) {
            return array();
        }

        $options = $question->getOptions();
        $histogram = array();
        $map = array();
        $i = 0;
        foreach ($options as $option) {
            $map[$option->getId()]=$i;
            $histogram[$i] =
                array('cnt' => 0,
                      'title' => $option->getOption(),
                      'value' => $option->getEvaluation()
                      );
            $i++;
        }

        foreach ($answers as $answer) {
            if ($answer->hasOption()) {
                $histogram[$map[$answer->getOption()->getId()]]['cnt'] += 1;
            }
        }

        return $histogram;
    }

    /**
     * Creates chart for given histogram
     */
    public function getChart(Question $question, $histogram) {
        $width = 300;
        $height = 150;

        if (count($histogram) == 2) {
            $palette = '338000|d40000';   // the first choice is the best
        }
        else if (count($histogram) == 5 && $histogram[2]['value'] == 0) {
            $palette = 'd40000|bae11e|338000|bae11e|d40000';   // the middle choice is the best
        }
        else if (count($histogram) == 5 && $histogram[0]['value'] > $histogram[4]['value']) {
            $palette = '338000|bae11e|b3b3b3|f1792a|d40000';   // the first choice is the best
        }
        else if (count($histogram) == 5) {
            $palette = 'd40000|f1792a|b3b3b3|bae11e|338000';   // the last choice is the best
        }
        else {
            $palette = '';
        }

        $titles = array_map(function ($data) { return $data['title']; }, $histogram);
        $counts = array_map(function ($data) { return $data['cnt']; }, $histogram);
        if (array_sum($counts) == 0) {
            return null;
        }
 
        // docs at http://code.google.com/apis/chart/image/docs/gallery/bar_charts.html
        $options = array(
            'cht' => 'bhs',   // bar chart, horizontal, stacked
            'chbh' => 'a',   // bar width = automatic
            'chds' => 'a',   // automatic data scaling
            'chxt' => 'x,y',   // visible axes
            // axis style: leave default color (676767) and font (11.5), align right (1) and
            // show only axis line (l) and not line with tick marks (lt)
            'chxs' => '1,676767,11.5,1,l',
            'chs' => $width . 'x' . $height,
            'chco' => $palette,
            'chxl' => '1:|' . implode('|', array_reverse($titles)),
            'chd' => 't:' . implode(',', $counts)
        );

        $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') .
            '://chart.googleapis.com/chart?' . http_build_query($options);

        return array('url' => $url, 'width' => $width, 'height' => $height);
    }

    /**
     * Returns statistics for given data
     *
     * @returns array with following items:
     *  - cnt count
     *  - avg (optional) average value
     *  - sigma (optional) standard deviation
     *  - confidence_value (optional) confidence for estimating interval
     *  - confidence_interval_{low,high} confidence interval range
     */
    public function getStatistics(array $histogram) {
        $data = array_map(function ($x) {return array($x['value'], $x['cnt']);}, $histogram);
        $cnt = StatisticalFunctions::cnt($data);
        $stats['cnt'] = $cnt;
        if ($cnt > 0) {
            $stats['avg'] = StatisticalFunctions::average($data);
            $stats['median'] = StatisticalFunctions::median($data);
        }
        if ($cnt > 1) {
            $stats['sigma'] = StatisticalFunctions::stddev($data);
            if (function_exists('stats_cdf_t')) {
                $confHalf = StatisticalFunctions::confidenceHalf($data, self::INTERVAL_CONFIDENCE);
                // Warning: we do not want to do this in statistical functions, as we need to get
                // minimum/maximum also of histogram items with count 0
                $values = array_map(function ($x) {return $x[0];}, $data);
                $stats['confidence_value'] = self::INTERVAL_CONFIDENCE;
                $stats['confidence_interval_low'] = max(min($values), $stats['avg'] - $confHalf);
                $stats['confidence_interval_high'] = min(max($values), $stats['avg'] + $confHalf);
            }
        }
        return $stats;
    }
    
    /**
     * Check if the array contains at least one pair of different values
     * @param array $array
     * @return boolean true if the array contains at least two different values
     */
    private function hasMoreThanOneValue($array)
    {
        $first = true;
        $prev = null;
        foreach ($array as $value) {
            if (!$first) {
                if ($prev !== $value) {
                    return true;
                }
            }
            $prev = $value;
            $first = false;
        }
        return false;
    }
    
    /**
     * Function returns the statistics for a question in a format:
     *      result['results'] - array of number of options indexed by option id
     *      result['chart'] - chart of the results, or null if question has no options
     *      result['comments'] - array of comments
     * 
     * @param Question $question
     * @param array $answers 
     * 
     * @return array see function description for more info
     */
    public function processQuestion(Question $question, $answers) {
        $histogram = $this->getHistogramData($question, $answers);
        $stats = $this->getStatistics($histogram);
        $comments = $this->getComments($answers);
        $hasComments = count($comments) > 0;
        $hasAnsweredOption = $stats['cnt'] > 0;
        if ($hasAnsweredOption) {
            foreach ($histogram as $key=>$value) {
                $histogram[$key]['portion'] = $value['cnt'] / $stats['cnt'];
            }
        }
        $evaluations = array();
        foreach ($question->getOptions() as $option) {
            $evaluations[] = $option->getEvaluation();
        }
        $hasDifferentOptions = $this->hasMoreThanOneValue($evaluations);
        if (!$hasDifferentOptions) {
            // Nema zmysel prezentovat zlozitejsiu statistiku pocitanu z rovnakych hodnot
            $stats = array('cnt' => $stats['cnt']);
        }
        if (!$hasAnsweredOption) {
            $histogram = array();
        }
        $data = array(
                'id' => $question->getId(),
                'title' => $question->getQuestion(),
                'description' => $question->getDescription(),
                'commentsAllowed' => $question->getHasComment(),
                'hasAnswer' => $hasAnsweredOption | $hasComments,
                'hasDifferentOptions' => $hasDifferentOptions,
                'comments' => $comments,
                'histogram' => $histogram,
                'chart' => $this->getChart($question, $histogram),
                'stats' => $stats,
                );

        return $data;
    }

    /**
     * Vrat nazov kategorie pre predmet
     * @param Subject $subject
     * @return string nazov kategorie alebo self::NO_CATEGORY ak je nekategorizovany
     */
    private function getCategory(Subject $subject)
    {
        $match = preg_match("@^[^-]*-([^-]*)-@", $subject->getCode(), $matches);
        if ($match == 0) {
            return self::NO_CATEGORY;
        } else {
            return $matches[1];
        }
    }

    // TODO:nahrad celu tuto saskaren studijnymi programmi ked budu k dispozicii
    public function getCategorizedSubjects(Season $season) {
        $em = $this->get('doctrine.orm.entity_manager');
        $subjects = $em->getRepository('AnketaBundle\Entity\Subject')->getSortedSubjectsWithAnswers($season);
        $categorized = array();
        $uncategorized = array();
        foreach ($subjects as $subject) {
            $category = $this->getCategory($subject);

            if ($category === self::NO_CATEGORY) {
                $uncategorized[] = $subject;
            } else {
                $categorized[$category][] = $subject;
            }
        }
        uksort($categorized, 'strcasecmp');
        // we want to append this after sorting
        if (!empty($uncategorized)) {
            $categorized[self::NO_CATEGORY] = $uncategorized;
        }
        return $categorized;
    }

    public function subjectsAction($season_slug, $category) {
        $em = $this->get('doctrine.orm.entity_manager');

        $season = $this->getSeason($season_slug);
        $templateParams = array();
        $subjects = $this->getCategorizedSubjects($season);

        if ($category == null) {
            $templateParams['categorized_subjects'] = $subjects;
        } else {
            if (!array_key_exists($category, $subjects)) {
                throw new NotFoundHttpException("Category '$category' not found");
            }
            $templateParams['categorized_subjects'] = array($category => $subjects[$category]);
        }
        $templateParams['season'] = $season;
        $templateParams['category'] = $category;
        return $this->render('AnketaBundle:Statistics:subjects.html.twig', $templateParams);
    }

    public function studyProgramsAction($season_slug) {
        $em = $this->get('doctrine.orm.entity_manager');

        $season = $this->getSeason($season_slug);
        $templateParams = array();

        $studyPrograms = $em->getRepository('AnketaBundle:StudyProgram')->findBy(array(), array('name' => 'ASC'));
        
        $templateParams['study_programs'] = $studyPrograms;
        $templateParams['season'] = $season;

        return $this->render('AnketaBundle:Statistics:studyPrograms.html.twig', $templateParams);
    }

    public function resultsSubjectAction($season_slug, $subject_code) {
        $em = $this->get('doctrine.orm.entity_manager');
        $season = $this->getSeason($season_slug);
        // TODO: check ci predmet s tym id patri do tejto sezony
        $subjectRepository = $em->getRepository('AnketaBundle:Subject');
        $subject = $subjectRepository->findOneBy(array('code' => $subject_code));
        if ($subject === null) {
            throw new NotFoundHttpException('Predmet ' . $subject_code . ' v sezone ' .
                        $season->getDescription() .
                        ' neexistoval.');
        }

        $category = $this->getCategory($subject);

        $maxCnt = 0;
        $results = array();

        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                       ->getOrderedQuestionsByCategoryType(CategoryType::SUBJECT);
        foreach ($questions as $question) {
            $answers = $em->getRepository('AnketaBundle\Entity\Answer')
                          ->findBy(array('question' => $question->getId(),
                                      'subject' => $subject->getId()));
            $data = $this->processQuestion($question, $answers);
            $maxCnt = max($maxCnt, $data['stats']['cnt']);
            $results[] = $data;
        }

        $responses = $em->getRepository('AnketaBundle:Response')
                        ->findBy(array('subject' => $subject->getId(), 'teacher' => null, 'studyProgram' => null));
        $templateParams['responses'] = $this->createUsernameFromLogin($responses);
        $templateParams['season'] = $season;
        $templateParams['category'] = $category;
        $templateParams['subject'] = $subject;

        if ($maxCnt >= self::MIN_VOTERS_FOR_PUBLIC ||
            $this->get('security.context')->isGranted('ROLE_FULL_RESULTS')) {
            $templateParams['results'] = $results;
            return $this->render('AnketaBundle:Statistics:resultsSubject.html.twig',
                                 $templateParams);
        } else {
            $templateParams['limit'] = self::MIN_VOTERS_FOR_PUBLIC;
            return $this->render('AnketaBundle:Statistics:requestResults.html.twig',
                                 $templateParams);
        }
    }

    public function resultsStudyProgramAction($season_slug, $program_slug) {
        $em = $this->get('doctrine.orm.entity_manager');
        $season = $this->getSeason($season_slug);
        // TODO: check ci predmet s tym id patri do tejto sezony
        $spRepository = $em->getRepository('AnketaBundle:StudyProgram');
        $studyProgram = $spRepository->findOneBy(array('slug' => $program_slug));
        if ($studyProgram === null) {
            throw new NotFoundHttpException('Studijny odbor so skratkou ' . $program_slug . ' neexistuje.');
        }

        $maxCnt = 0;
        $results = array();

        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                       ->getOrderedQuestionsByCategoryType(CategoryType::STUDY_PROGRAMME);
        foreach ($questions as $question) {
            $answers = $em->getRepository('AnketaBundle\Entity\Answer')
                          ->findBy(array('question' => $question->getId(),
                                      'studyProgram' => $studyProgram->getId()));
            $data = $this->processQuestion($question, $answers);
            $maxCnt = max($maxCnt, $data['stats']['cnt']);
            $results[] = $data;
        }

        $responses = $em->getRepository('AnketaBundle:Response')
                        ->findBy(array('studyProgram' => $studyProgram->getId(), 'teacher' => null, 'subject' => null));
        $templateParams['responses'] = $this->createUsernameFromLogin($responses);
 
        $templateParams['season'] = $season;
        $templateParams['studyProgram'] = $studyProgram;
        
        if ($maxCnt >= self::MIN_VOTERS_FOR_PUBLIC ||
            $this->get('security.context')->isGranted('ROLE_FULL_RESULTS')) {
            $templateParams['results'] = $results;
            return $this->render('AnketaBundle:Statistics:resultsStudyProgram.html.twig',
                                 $templateParams);
        } else {
            $templateParams['limit'] = self::MIN_VOTERS_FOR_PUBLIC;
            return $this->render('AnketaBundle:Statistics:requestResults.html.twig',
                                 $templateParams);
        }
    }

    public function resultsSubjectTeacherAction($season_slug, $subject_code, $teacher_id) {
        $em = $this->get('doctrine.orm.entity_manager');
        $season = $this->getSeason($season_slug);
        // TODO: check ci predmet s tym id patri do tejto sezony
        $subjectRepository = $em->getRepository('AnketaBundle:Subject');
        $subject = $subjectRepository->findOneBy(array('code' => $subject_code));
        if ($subject === null) {
            throw new NotFoundHttpException('Predmet ' . $subject_code . ' v sezone ' .
                        $season->getDescription() .
                        ' neexistoval.');
        }
        $category = $this->getCategory($subject);
        $teacher = $em->find('AnketaBundle:Teacher', $teacher_id);
        if ($teacher === null) {
            throw new NotFoundHttpException('Učiteľ ' . $teacher_id . ' neexistuje');
        }

        $maxCnt = 0;
        $results = array();

        $questions = $em->getRepository('AnketaBundle\Entity\Question')
                       ->getOrderedQuestionsByCategoryType(CategoryType::TEACHER_SUBJECT);
        foreach ($questions as $question) {
            $answers = $em->getRepository('AnketaBundle\Entity\Answer')
                          ->findBy(array('question' => $question->getId(),
                                      'subject' => $subject->getId(),
                                      'teacher' => $teacher->getId()));
            $data = $this->processQuestion($question, $answers);
            $maxCnt = max($maxCnt, $data['stats']['cnt']);
            $results[] = $data;
        }
        
        $responses = $em->getRepository('AnketaBundle:Response')
                        ->findBy(array('subject' => $subject->getId(), 'teacher' => $teacher_id, 'studyProgram' => null));
        $templateParams['responses'] = $this->createUsernameFromLogin($responses);
        $templateParams['season'] = $season;
        $templateParams['category'] = $category;
        $templateParams['subject'] = $subject;
        $templateParams['teacher'] = $teacher;
        
        if ($maxCnt >= self::MIN_VOTERS_FOR_PUBLIC ||
            $this->get('security.context')->isGranted('ROLE_FULL_RESULTS')) {
            $templateParams['results'] = $results;
            return $this->render('AnketaBundle:Statistics:resultsSubjectTeacher.html.twig',
                                 $templateParams);
        } else {
            $templateParams['limit'] = self::MIN_VOTERS_FOR_PUBLIC;
            return $this->render('AnketaBundle:Statistics:requestResults.html.twig',
                                 $templateParams);
        }
    }

    public function getMenuRoot($season) {
        $em = $this->get('doctrine.orm.entity_manager');
        
        $current = array(
                'general' => new MenuItem(
                    'Všeobecné otázky',
                    $this->generateUrl('statistics_general',
                        array('season_slug' => $season->getSlug()))),
                'study_programs' => new MenuItem(
                    'Študijné odbory',
                    $this->generateUrl('statistics_study_programs',
                        array('season_slug' => $season->getSlug()))),
                'subjects' => new MenuItem(
                    'Predmety',
                    $this->generateUrl('statistics_subjects',
                        array('season_slug' => $season->getSlug()))),
                );

        $seasons = $em->getRepository('AnketaBundle\Entity\Season')
                    ->findAll(array());
        $menu = array();
        foreach ($seasons as $tmp) {
            $menu[$tmp->getId()] = new MenuItem($tmp->getDescription(),
                    $this->generateUrl('statistics_general',
                        array('season_slug' => $season->getSlug())));
        }
        
        $menu[$season->getId()]->children = $current;
        $menu[$season->getId()]->expanded = true;
        return $menu;
    }

    public function menuAction($season) {
        $menu = $this->getMenuRoot($season);
        $menu[$season->getId()]->active = true;
        $templateParams = array('menu' => $this->getMenuRoot($season));
        return $this->render('AnketaBundle:Hlasovanie:menu.html.twig',
                             $templateParams);
    }

    public function menuVseobecneAction($season) {
        $menu = $this->getMenuRoot($season);
        $menu[$season->getId()]->children['general']->active = true;
        $templateParams = array('menu' => $menu);
        return $this->render('AnketaBundle:Hlasovanie:menu.html.twig',
                             $templateParams);

    }

     public function menuStudijneOdboryAction($season, $program_code = null) {
        $menu = $this->getMenuRoot($season);
        $studyProgramsMenu = $menu[$season->getId()]->children['study_programs'];
        $studyProgramsMenu->expanded = true;

        $em = $this->get('doctrine.orm.entity_manager');
        $studyPrograms = $em->getRepository('AnketaBundle:StudyProgram')->findBy(array(), array('name' => 'ASC'));
        $studyProgramsMenu->children = array();
        foreach ($studyPrograms as $sp) {
            $studyProgramsMenu->children[$sp->getCode()] = new MenuItem(
                    $sp->getCode(),
                    $this->generateUrl('statistics_study_program',
                        array('season_slug' => $season->getSlug(), 'program_slug' => $sp->getSlug())));
        }

        if ($program_code == null) {
            $studyProgramsMenu->active = true;
        } else {
            $studyProgramsMenu->children[$program_code]->active = true;
        }
        $templateParams = array('menu' => $menu);
        return $this->render('AnketaBundle:Hlasovanie:menu.html.twig',
                             $templateParams);

    }

    public function menuPredmetyAction($season, $category=null, $subject_id=-1, $teacher_id=-1) {
        $menu = $this->getMenuRoot($season);
        $subjects_menu = $menu[$season->getId()]->children['subjects'];
        $subjects_menu->expanded = true;
        
        $subjects = $this->getCategorizedSubjects($season);
        $subjects_menu->children = array();
        foreach (array_keys($subjects) as $key) {
            $subjects_menu->children[$key] = new MenuItem(
                    $key,
                    $this->generateUrl('statistics_subjects',
                        array('season_slug' => $season->getSlug(), 'category' => $key)));
        }

        if ($category == null) {
            $subjects_menu->active = true;
        } else {
            if (!array_key_exists($category, $subjects)) {
                throw new NotFoundHttpException("Category '$category' not found");
            }
            $subjects_menu->only_expanded = true;
            $category_menu = $subjects_menu->children[$category];
            $category_menu->expanded = true;
            foreach ($subjects[$category] as $subj) {
                $category_menu->children[$subj->getId()] = new MenuItem(
                        $subj->getName(),
                        $this->generateUrl('results_subject',
                            array('season_slug' => $season->getSlug(),
                                  'subject_code' => $subj->getCode())));
            }
            $category_menu->expanded = true;
            if ($subject_id == -1) {
                $category_menu->active = true;
            } else {
                $category_menu->only_expanded = true;
                $subject_menu = $category_menu->children[$subject_id];
                $subject_menu->expanded = true;
                
                $em = $this->get('doctrine.orm.entity_manager');
                $subject = $em->find('AnketaBundle:Subject', $subject_id);
                if ($subject == null) {
                    throw new NotFoundHttpException("Subject not found");
                }
                $teacherRepository = $em->getRepository('AnketaBundle:Teacher');
                $teachers = $teacherRepository->getTeachersForSubject($subject, $season);
                foreach ($teachers as $teacher) {
                    $subject_menu->children[$teacher->getId()] = new MenuItem(
                            $teacher->getName(),
                            $this->generateUrl('results_subject_teacher',
                                array('season_slug' => $season->getSlug(),
                                      'subject_code' => $subject->getCode(),
                                      'teacher_id' => $teacher->getId()))
                        );
                }
                
                if ($teacher_id == -1) {
                    $subject_menu->active = true;
                } else {
                    $subject_menu->children[$teacher_id]->active = true;
                }

            }
        }


        $templateParams = array('menu' => $menu);
        return $this->render('AnketaBundle:Hlasovanie:menu.html.twig',
                             $templateParams);

    }

    public function generalAction($season_slug = null) {
        $em = $this->get('doctrine.orm.entity_manager');
        $season = $this->getSeason($season_slug);
        // TODO: by season
        $categories = $em->getRepository('AnketaBundle\Entity\Category')
                         ->findBy(array('type' => 'general'));
        foreach ($categories AS $category) {
            // TODO: by season
            $questions[$category->getId()] = $em->getRepository('AnketaBundle\Entity\Question')
                                                ->getOrderedQuestions($category);
        }

        $templateParams = array();
        $templateParams['questions'] = $questions;
        $templateParams['categories'] = $categories;
        $templateParams['season'] = $season;
        return $this->render('AnketaBundle:Statistics:general.html.twig', $templateParams);
    }

    public function resultsGeneralAction($season_slug, $question_id) {
        $em = $this->get('doctrine.orm.entity_manager');
        // TODO: check ci otazka s tym id patri do tejto sezony
        $season = $this->getSeason($season_slug);
        // TODO: validacia ci ta otazka je vseobecna
        $question = $em->find('AnketaBundle:Question', $question_id);
        if ($question == null) {
            throw new NotFoundHttpException("Otázka s daným id neexistuje");
        }
        $answers = $em->getRepository('AnketaBundle\Entity\Answer')
                      ->findBy(array('question' => $question->getId()));

        $responses = $em->getRepository('AnketaBundle:Response')
                        ->findBy(array('question' => $question_id));
        $templateParams['responses'] = $this->createUsernameFromLogin($responses);
        $templateParams['result'] = $this->processQuestion($question, $answers);
        $templateParams['season'] = $season;
        return $this->render('AnketaBundle:Statistics:resultsGeneral.html.twig', $templateParams);
    }

    private function createUsernameFromLogin($responses)
    {
        foreach ($responses as $response)
        {
            if ($response->getAuthorLogin())
            {
                $em = $this->get('doctrine.orm.entity_manager');
                $user = $em->getRepository('AnketaBundle:User')
                           ->findOneBy(array('userName' => $response->getAuthorLogin()));
                if (!empty($user)) $response->setAuthorText($user->getDisplayName());
            }
        }
        return $responses;
    }

}
