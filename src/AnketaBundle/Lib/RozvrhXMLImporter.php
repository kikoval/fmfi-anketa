<?php

/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk@gmail.com>
 */

namespace AnketaBundle\Lib;

use PDO;
use SVT\RozvrhXML\Importer;

/**
 * Customized RozvrhXMLImporter that adds additional columns
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class RozvrhXMLImporter extends Importer {

    /** @var SubjectIdentification */
    private $subjectIdentification;
    
    /** @var array('katedra' => 'code') */
    private $katedraCodeMap;

    public function __construct(PDO $connection, SubjectIdentificationInterface $subjectIdentification,
            array $katedraCodeMap) {
        parent::__construct($connection);
        $this->subjectIdentification = $subjectIdentification;
        $this->katedraCodeMap = $katedraCodeMap;
    }

    protected function getTableDefinitions() {
        $tables = parent::getTableDefinitions();
        $tables['predmet']['columns']['slug'] = 'varchar(255)';
        return $tables;
    }

    protected function convertPredmet(array $location, array $predmet) {
        $predmet = parent::convertPredmet($location, $predmet);
        $props = $this->subjectIdentification->identify($predmet['code'], $predmet['name']);
        $predmet['name'] = $props['name'];
        $predmet['code'] = $props['code'];
        $predmet['slug'] = $props['slug'];
        return $predmet;
    }
    
    protected function convertUcitel(array $location, array $ucitel) {
        $ucitel = parent::convertUcitel($location, $ucitel);
        if ($ucitel['katedra'] !== null) {
            if (!array_key_exists($ucitel['katedra'], $this->katedraCodeMap)) {
                $ucitel['katedra'] = null;
            }
            else {
                $ucitel['katedra'] = $this->katedraCodeMap[$ucitel['katedra']];
            }
        }
        return $ucitel;
    }
}