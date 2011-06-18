<?php
/**
 * This file contains exception thrown during statistical computations
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Lib
 * @author     Peter Perešíni <ppershing@gmail.com>
 */
namespace AnketaBundle\Lib;
use RuntimeException;

/**
 * Exception throws during statistical computations
 * if the required statistics cannot be computed for
 * some reason
 */
class StatisticalException extends RuntimeException {
}
