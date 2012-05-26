<?php
/**
 * This file contains LDAP data retriever
 *
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Integration
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Integration;

class LDAPRetriever {

    private $linkId;
    private $serverUrl;
    private $baseDN;
    
    public function __construct($serverUrl, $baseDN)
    {
        $this->serverUrl = $serverUrl;
        $this->baseDN = $baseDN;
        $this->linkId = null;
    }

    public function loginIfNotAlready()
    {
        if ($this->linkId !== null) return;
        $this->linkId = @ldap_connect($this->serverUrl);
        if ($this->linkId === false) {
            $this->linkId = null;
            throw new \Exception('Failed to create LDAP resource');
        }
        
        // Toto treba pre JAS-ovsky server
        if (!ldap_set_option($this->linkId, LDAP_OPT_PROTOCOL_VERSION, 3)) {
            $this->throwException();
        }
        
        // Bindneme sa
        if (@!ldap_bind($this->linkId)) {
            $this->throwException();
        }
        
    }
    
    public function logoutIfNotAlready()
    {
        if ($this->linkId !== null) {
            ldap_unbind($this->linkId);
            $this->linkId = null;
        }
    }

    private function throwException() {
        $errno = ldap_errno($this->linkId);
        $error = ldap_error($this->linkId);
        throw new \Exception('LDAP ' . $errno . ': ' . $error);
    }
    
    private function fetchAttributes($entry) {
        $attrs = array();
        $attr = @ldap_first_attribute($this->linkId, $entry);
        if ($attr === false) {
            return $attrs;
        }
        $attrs[] = $attr;
        while (($attr = @ldap_next_attribute($this->linkId, $entry)) !== false) {
            $attrs[] = $attr;
        }
        return $attrs;
    }
    
    private function fetchEntry($entry, $attributes)
    {
        $data = array();
        foreach ($attributes as $attribute) {
            $data[$attribute] = array();
        }
        
        foreach ($this->fetchAttributes($entry) as $attribute) {
            $ldapValues = @ldap_get_values($this->linkId, $entry, $attribute);
            if ($ldapValues === false) {
                $this->throwException();
            }
            for ($i=0; $i < $ldapValues['count']; $i++) {
                $data[$attribute][] = $ldapValues[$i];
            }
        }
        return $data;
    }
    
    private function freeResult($result)
    {
        $ret = @ldap_free_result($result);
        if ($ret === false) {
            $this->throwException();
        }
    }
    
    private function firstEntry($result)
    {
        $entry = @ldap_first_entry($this->linkId, $result);
        if ($entry === false) {
            $this->throwException();
        }
        return $entry;
    }
    
    private function nextEntry($entry)
    {
        $entry = @ldap_next_entry($this->linkId, $entry);
        if ($entry === false) {
            $this->throwException();
        }
        return $entry;
    }
    
    private function getCount($result)
    {
        $count = @ldap_count_entries($this->linkId, $result);
        if ($count === false) {
            $this->throwException();
        }
        return $count;
    }
    
    private function runSearch($filter, $attributes)
    {
        $result = @ldap_search($this->linkId, $this->baseDN, $filter, $attributes);
        if ($result === false) {
            $this->throwException();
        }
        return $result;
    }

    public function searchOne($filter, $attributes)
    {
        $this->loginIfNotAlready();
        $result = $this->runSearch($filter, $attributes);
        $count = $this->getCount($result);
        
        if ($count === 0) {
            return null;
        }
        
        if ($count !== 1) {
            throw new \Exception('Only one result exected');
        }
        
        $entry = $this->firstEntry($result);
        $data = $this->fetchEntry($entry, $attributes);
        
        $this->freeResult($result);
        
        return $data;
    }
    
    public function searchAll($filter, $attributes, $limit)
    {
        $this->loginIfNotAlready();
        $result = $this->runSearch($filter, $attributes);
        
        $count = $this->getCount($result);
        
        $data = array();
        
        if ($count === 0) {
            return $data;
        }
        
        $entry = $this->firstEntry($result);
        $data[] = $this->fetchEntry($entry, $attributes);
        
        for ($i = 1; $i < $count; $i++) {
            $entry = $this->nextEntry($entry);
            $data[] = $this->fetchEntry($entry, $attributes);
        }
        
        $this->freeResult($result);
        
        return $data;
        
    }
    
    /**
     * Escape LDAP filter value
     * @see http://www.ietf.org/rfc/rfc2254.txt
     * @param string $str
     * @return strign the escaped string
     */
    public function escape($str)
    {
        if (!is_string($str)) {
            throw new \InvalidArgumentException('Only strings can be escaped');
        }
        $esc = '';
        // NOTE: strlen does not always work for binary strings
        // as it can be overriden by mbstring.func_overload 2
        $len = mb_strlen($str, 'latin1');
        for ($i = 0; $i < $len; $i++) {
            $char = $str[$i];
            $needs_escape = in_array($char, array('*', '(', ')', '\\', "\0"));
            if ($needs_escape) {
                $esc .= '\\';
                $esc .= substr('00'.dechex(ord($char)), -2);
            }
            else {
                $esc .= $char;
            }
        }
        return $esc;
    }

}
