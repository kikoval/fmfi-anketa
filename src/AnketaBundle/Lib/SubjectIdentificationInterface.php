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

interface SubjectIdentificationInterface
{
    
    /**
     * Map long code and subject name to unique subject slug
     * @return string unique slug
     */
    public function generateSlug($longCode, $subjectName);
    
    /**
     * Generate UI strings for subject
     * @return array('code' => code, 'name' => name)
     */
    public function generateUIStrings($longCode, $subjectName);
    
}