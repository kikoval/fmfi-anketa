<?php
/**
 * @copyright Copyright (c) 2013 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Integration
 * @author     Martin Kralik <majak47@gmail.com>
 */

namespace AnketaBundle\Integration;
use AnketaBundle\Integration\LDAPRetriever;

/**
 * Searches LDAP for teachers.
 *
 * @author Martin Kralik <majak47@gmail.com>
 */
class LDAPTeacherSearch {

    private $ldap;
    private $orgUnit;
    const GROUP_REGEXP = '@^pouzivatelia_(?P<orgUnits>[a-zA-Z]+)(?<!interni|externi)$@';

    public function __construct(LDAPRetriever $ldap, $orgUnit) {
        $this->ldap = $ldap;
        $this->ldap->loginIfNotAlready();
        $this->orgUnit = $orgUnit;
    }

    public function __destruct() {
        $this->ldap->logoutIfNotAlready();
    }
    
    /**
     * Searches LDAP for users by substring of their full name (without accents).
     * In addition, users must be either teachers on any faculty or PhD students
     * on faculty provided in class constructor.
     *
     * @param string $name Substring of name
     * @return array @see executeSeachAndProcessData for docs
     */
    public function byFullName($name) {
        $safeName = $this->ldap->escape($name);
        $safeOrgUnit = $this->ldap->escape($this->orgUnit);
        $filter = '(&(cn=*'.$safeName.'*)(|(group=zamestnanci)(group=doktorandi_'.$safeOrgUnit.')))';
        
        return $this->executeSeachAndProcessData($filter);
    }

    /**
     * Searches LDAP for user(s) based on a full login.
     * In addition, users must be either teachers on any faculty or PhD students
     * on faculty provided in class constructor.
     *
     * @param string $login full login
     * @return array @see executeSeachAndProcessData for docs
     */
    public function byLogin($login) {
        $safeLogin = $this->ldap->escape($login);
        $safeOrgUnit = $this->ldap->escape($this->orgUnit);
        $filter = '(&(uid='.$safeLogin.')(|(group=zamestnanci)(group=doktorandi_'.$safeOrgUnit.')))';
        
        return $this->executeSeachAndProcessData($filter);
    }
    
    /**
     * Executes LDAP search and returns results as associated array.
     *
     * Number of results is capped by settings on used LDAP server.
     *
     * Result array has user logins as keys and full name with all titles
     * followed by their faculties as values.
     *
     * @example Return value for $name='kralik' could look like this:
     *  array(5) {
     *    ["kralik1"]=> array(2) {
     *      ["name"]=> string(26) "RNDr. Eduard Králik, CSc."
     *      ["givenName"]=> "Eduard",
     *      ["familyName"]=> "Králik",
     *      ["orgUnits"]=> array(1) { [0]=> string(4) "PriF" }
     *    }
     *    ["kralik3"]=> array(2) {
     *      ["name"]=> string(14) "Martin Králik"
     *      ["givenName"]=> "Martin",
     *      ["familyName"]=> "Králik",
     *      ["orgUnits"]=> array(1) { [0]=> string(4) "FMFI" }
     *    }
     *    ...
     *  }
     *
     * @param string $filter
     * @return array
     */
    private function executeSeachAndProcessData($filter) {
        $result = $this->ldap->searchAll($filter,
                array('displayName', 'uid', 'group', 'givenNameU8', 'snU8'));
        
        $teachers = array();
        foreach ($result as $record) {
            $teachers[$record['uid'][0]]['name'] = $record['displayName'][0];
            $teachers[$record['uid'][0]]['givenName'] = $record['givenNameU8'][0];
            $teachers[$record['uid'][0]]['familyName'] = $record['snU8'][0];
            $orgUnits = array();
            foreach ($record['group'] as $group) {
                $match = array();
                if (preg_match(self::GROUP_REGEXP, $group, $match)) {
                    $orgUnits[] = $match['orgUnits'];
                }
            }
            $teachers[$record['uid'][0]]['orgUnits'] = $orgUnits;
        }
        return $teachers;
    }

}

?>
