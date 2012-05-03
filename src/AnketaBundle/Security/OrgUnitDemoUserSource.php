<?php
/**
 * This file contains user source that assigns all subjects to the user,
 * and grants him a voting right. Useful for demo version, where we can't use
 * AIS to provide such information.
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

use Doctrine\ORM\EntityManager;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\UserSeason;
use AnketaBundle\Entity\UsersSubjects;
use AnketaBundle\Entity\Subject;
use AnketaBundle\Integration\AISRetriever;
use AnketaBundle\Entity\Role;

class OrgUnitDemoUserSource implements UserSourceInterface
{
    
    /** @var array */
    private $orgUnits;

    public function __construct(array $orgUnits = null)
    {
        $this->orgUnits = $orgUnits;
    }

    public function load(UserSeason $userSeason)
    {
        $user = $userSeason->getUser();

        if ($this->orgUnits !== null) {
            $user->setOrgUnits($this->orgUnits);
        }
        $userSeason->setIsStudent(true);
        $userSeason->setFinished(false);
        
        return true;
    }
}
