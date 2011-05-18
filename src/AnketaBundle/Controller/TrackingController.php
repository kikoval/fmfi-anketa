<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Controller
 * @author     Peter Peresini <ppershing@gmail.com>
 */

/**
 * Controller for tracking site usage
 */

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TrackingController extends Controller {

    const TRACK_CODE_PARAMETER = 'google_analytics_tracking_code';
    
    public function trackAction() {
        if ($this->container->hasParameter(self::TRACK_CODE_PARAMETER)) {
            $ga = $this->container->getParameter(self::TRACK_CODE_PARAMETER);
        }
        else {
            $ga = null;
        }
        return $this->render('AnketaBundle:Tracking:analytics.html.twig',
                        array('ga_tracking_code' => $ga));
    }


}
