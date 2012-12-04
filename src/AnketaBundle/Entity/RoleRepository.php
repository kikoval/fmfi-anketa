<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Entity__Repository
 * @author     Martin Sucha <anty.sk+svt@googlegroups.com>
 */

namespace AnketaBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AnketaBundle\Entity\Role;

class RoleRepository extends EntityRepository {

    public function findOrCreateRole($name) {
        $role = $this->findOneBy(array('name' => $name));
        if ($role == null) {
            $role = new Role($name);
            $this->_em->persist($role);
        }
        return $role;
    }

}