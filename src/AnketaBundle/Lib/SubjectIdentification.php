<?php
/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Lib;

class SubjectIdentification implements SubjectIdentificationInterface
{
    
    /**
     * Map long code and subject name to unique subject slug
     * @return string unique slug
     */
    public function generateSlug($longCode, $subjectName) {
        $faculty = $this->getFaculty($longCode);
        if ($faculty == 'FMFI') {
            return $this->slugify($this->getShortCode($code));
        }
        else {
            return $this->slugify($longCode . '-' . $subjectName);
        }
    }
    
    /**
     * Generate UI strings for subject
     * @return array('code' => code, 'name' => name)
     */
    public function generateUIStrings($longCode, $subjectName) {
        $faculty = $this->getFaculty($longCode);
        if ($faculty == 'FMFI') {
            return array(
                'code' => $this->getShortCode($code),
                'name' => $subjectName,
                );
        }
        else {
            return array(
                'code' => $longCode,
                'name' => $subjectName,
            );
        }
    }
    
    private function transliterate($string)
    {
        $oldLocale = setlocale(LC_CTYPE, "0");
        setlocale(LC_CTYPE, 'sk_SK.utf-8');
        $result = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        setlocale(LC_CTYPE, $oldLocale);
        return $result;
    }
    
    private function slugify($string)
    {
        $slug = $this->transliterate($string);
        $slug = preg_replace('@[^a-zA-Z0-9_]@', '-', $slug);
        $slug = preg_replace('@-+@', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    private function getFaculty($longCode)
    {
        $parts = explode(".", $longCode);
        return $parts[0];
    }
    
    private function getShortCode($longCode)
    {
        $matches = array();
        if (preg_match('@^[^/]*/([^/]+)/@', $longCode, $matches) !== 1) {
            // Nevieme zistit kratky kod
            return $longCode;
        }

        return $matches[1];
    }
    
}