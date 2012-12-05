<?php
/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Controller
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for proper login with cosign on pages with allowed public access
 * TODO: probably move to a new authentication listener
 */
class LoginController extends Controller {

    /**
     * This action just redirects the user as login is handled by the cosign
     * authentication listener. But as almost everything has public access
     * allowed, we need a way to trigger cosign authentication, therefore
     * this action is set to cosign protected mode, which triggers authentication
     * as necessary.
     */
    public function loginAction() {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            // By this point, we should be already logged in. If we're not,
            // the web server isn't properly configured.
            throw new \Exception('/login is supposed to have "CosignAllowPublicAccess Off"');
        }

        $request = $this->get('request');
        if ($request->query->has('to')) {
            // We perform a basic check that the user is not redirected
            // outside our application by checking the prefix of the
            // request URI
            $base = $request->getUriForPath('/');
            $to = $request->query->get('to');
            if (0 === strpos($to, $base)) {
                // "to" is OK, so perform the redirect
                return new RedirectResponse($to);
            }
        }
        // "to" is either not set or invalid, so redirect to homepage
        return new RedirectResponse($this->generateUrl('homepage'));
    }

}