<?php
/**
 * This is an user source that doesn't load anything.
 *
 * @copyright Copyright (c) 2011,2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Security
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */

namespace AnketaBundle\Security;

use AnketaBundle\Entity\UserSeason;

class NoneUserSource implements UserSourceInterface
{

    public function load(UserSeason $userSeason, array $want)
    {
    }
}
