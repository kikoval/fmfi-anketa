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
     * Map long code and subject name (information received from AIS) to
     * internally usable information - a code and name (which are only
     * displayed in the UI, never used as keys) and a slug that can actually
     * uniquely identify the subject.
     *
     * @return array('code' => code, 'name' => name, 'slug' => slug)
     */
    public function identify($longCode, $subjectName);

}