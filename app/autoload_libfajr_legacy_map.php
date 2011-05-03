<?php
/**
 * Map file containing a list of legacy named libfajr classes.
 *
 * This map enables MapFileClassLoader to load classes that
 * the UniversalClassLoader cannot load because of underscores in their names.
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Controller
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

function legacyMap(array $array) {
    $dir = '/../vendor/libfajr/fajr/libfajr/';
    $ns = 'fajr\\libfajr\\';
    $ret = array();
    foreach ($array as $value) {
        $className = $ns.str_replace('/', '\\', $value);
        $relative = str_replace('/', DIRECTORY_SEPARATOR, $dir . $value);
        $fileName = __DIR__ . $relative .'.php';
        $ret[$className] = $fileName;
    }
    return $ret;
}

return legacyMap(array(
    'data_manipulation/AIS2ApplicationAvailabilityParser',
    'data_manipulation/AIS2TableParser',
    'data_manipulation/AIS2UserNameParser',
    'data_manipulation/AIS2Version',
    'data_manipulation/AIS2VersionParser',
    'data_manipulation/CosignProxyFileParser',
    'data_manipulation/DataTableImpl',
    'data_manipulation/InformacnyListAttributeEnum',
    'data_manipulation/InformacnyListDataImpl',
    'pub/data_manipulation/InformacnyListData',
    'pub/data_manipulation/SimpleDataTable',
    'pub/data_manipulation/Znamka',
    'pub/regression/fake_data/FakeData',
    'pub/window/VSES017_administracia_studia/AdministraciaStudiaScreen',
    'pub/window/VSES017_administracia_studia/HodnoteniaPriemeryScreen',
    'pub/window/VSES017_administracia_studia/PrehladKreditovDialog',
    'pub/window/VSES017_administracia_studia/TerminyDialog',
    'pub/window/VSES017_administracia_studia/TerminyHodnoteniaScreen',
    'pub/window/VSES017_administracia_studia/VSES017_Factory',
    'pub/window/VSES017_administracia_studia/VSES017_FactoryImpl',
    'pub/window/VSES017_administracia_studia/VSES017_FakeFactoryImpl',
    'pub/window/VSES017_administracia_studia/ZoznamPrihlasenychDialog',
    'window/VSES017_administracia_studia/fake/FakeAdministraciaStudiaScreenImpl',
    'window/VSES017_administracia_studia/fake/FakeHodnoteniaPriemeryScreenImpl',
    'window/VSES017_administracia_studia/fake/FakePrehladKreditovDialogImpl',
    'window/VSES017_administracia_studia/fake/FakeTerminyDialogImpl',
    'window/VSES017_administracia_studia/fake/FakeTerminyHodnoteniaScreenImpl',
    'window/VSES017_administracia_studia/fake/FakeZoznamPrihlasenychDialogImpl',
    'window/VSES017_administracia_studia/AdministraciaStudiaScreenImpl',
    'window/VSES017_administracia_studia/HodnoteniaPriemeryScreenImpl',
    'window/VSES017_administracia_studia/PrehladKreditovDialogImpl',
    'window/VSES017_administracia_studia/TerminyDialogImpl',
    'window/VSES017_administracia_studia/TerminyHodnoteniaScreenImpl',
    'window/VSES017_administracia_studia/ZoznamPrihlasenychDialogImpl',
));