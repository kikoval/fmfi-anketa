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
 * Description of LDAPTeacherSearch
 *
 * @author Martin Kralik <majak47@gmail.com>
 */
class LDAPTeacherSearch {

    private $ldap;
    private $orgUnit;
    const GROUP_REGEXP = '@^pouzivatelia_(?P<sucast>[a-zA-Z]+)(?<!interni|externi)$@';

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
     * In addition, users must be are either teachers on any faculty
     * or PhD students on faculty provided in class constructor.
     *
     * Number of results is capped by settings on used LDAP server.
     *
     * Result array has user logins as keys and full name with all titles
     * followed by their faculties as values.
     *
     * Return value for $name='kralik' could look like this:
     *    array(5) {
     *      ["kralikova15"]=>
     *      string(30) "Mgr. Silvia Králiková (PriF)"
     *      ["kralik3"]=>
     *      string(21) "Martin Králik (FMFI)"
     *      ["kralik1"]=>
     *`     string(33) "RNDr. Eduard Králik, CSc. (PriF)"
     *      ["kralik24"]=>
     *      string(32) "MUDr. Róbert Králik, PhD. (LF)"
     *      ["kralik2"]=>
     *      string(25) "RNDr. Tibor Králik (RUK)"
     *    }
     *
     * @param string $name Substring of name
     * @return array
     */
    public function byFullName($name) {
        $filter = '(&(cn=*'.$name.'*)(|(group=zamestnanci)(group=doktorandi_'.$this->orgUnit.')))';
        $result = $this->ldap->searchAll($filter, array('displayName', 'uid', 'group'));

        $teachers = array();
        foreach ($result as $record) {
            $teachers[$record['uid'][0]] = $record['displayName'][0];
            $groups = array();
            foreach ($record['group'] as $group) {
                $match = array();
                if (preg_match(self::GROUP_REGEXP, $group, $match)) {
                    $groups[] = $match['sucast'];
                }
            }
            if ($groups) $teachers[$record['uid'][0]] .= ' ('.  implode(', ', $groups).')';
        }
        return $teachers;
    }
}

?>
