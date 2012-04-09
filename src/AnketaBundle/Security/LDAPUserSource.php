<?php
/**
 * This file contains LDAP user source implementation
 *
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Security
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Security;

use AnketaBundle\Integration\LDAPRetriever;
use AnketaBundle\Entity\UserSeason;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class LDAPUserSource implements UserSourceInterface
{

    /** @var LDAPRetriever */
    private $ldapRetriever;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LDAPRetriever $ldapRetriever, LoggerInterface $logger = null)
    {
        $this->ldapRetriever = $ldapRetriever;
        $this->logger = $logger;
    }

    public function load(UserSeason $userSeason)
    {
        $user = $userSeason->getUser();
        $uidFilter = '(uid=' . $this->ldapRetriever->escape($user->getUserName()) . ')';
        
        if ($this->logger !== null) {
            $this->logger->info(sprintf('LDAP search with filter: %s', $uidFilter));
        }
        
        $this->ldapRetriever->loginIfNotAlready();
        try {
            $userInfo = $this->ldapRetriever->searchOne($uidFilter, array('group', 'displayName'));
            $this->ldapRetriever->logoutIfNotAlready();
        }
        catch(\Exception $e) {
            $this->ldapRetriever->logoutIfNotAlready();
            throw $e;
        }
        
        if ($userInfo === null) {
            if ($this->logger !== null) {
                $this->logger->info(sprintf('User %s not found in LDAP'));
            }
            return false;
        }
        
        if (!$user->hasDisplayName() && isset($userInfo['displayName']) &&
          count($userInfo['displayName']) > 0) {
            $user->setDisplayName($userInfo['displayName'][0]);
        }
        
        $orgUnits = array();
        foreach ($userInfo['group'] as $group) {
            $matches = array();
            if (preg_match('/^pouzivatelia_([^_]+)$/', $group, $matches) === 1) {
                $orgUnits[] = $matches[1];
            }
        }
        
        $user->setOrgUnits($orgUnits);
        return true;
    }

}