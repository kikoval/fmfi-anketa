<?php
/**
 * @copyright Copyright (c) 2010-2013 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Controller
 */

namespace AnketaBundle\Controller;

/**
 * Interface used to determine in which controller should be subjects imported
 * from AIS. When implemented by a controller, the import is executed BEFORE
 * an action method is called (and after the object has been created).
 *
 */
interface SubjectImportController {

}

?>
