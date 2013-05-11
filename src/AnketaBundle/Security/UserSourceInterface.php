<?php
/**
 * This file contains user source interface
 *
 * @copyright Copyright (c) 2011,2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Security
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Security;
use AnketaBundle\Entity\UserSeason;

interface UserSourceInterface
{

    /**
     * Load information about user
     * @param UserSeason $userSeason user-season to populate
     * @param array $want which user attributes are to be loaded
     */
    public function load(UserSeason $userSeason, array $want);

}