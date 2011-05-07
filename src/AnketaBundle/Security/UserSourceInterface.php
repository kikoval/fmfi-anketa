<?php
/**
 * This file contains user source interface 
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Security
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Security;

interface UserSourceInterface
{

    /**
     * Load information about user and store it to the builder
     * @param UserBuilder $builder the builder to populate
     */
    public function load(UserBuilder $builder);

}