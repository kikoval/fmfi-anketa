<?php
/**
 * This file contains tests for Statistical functions
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 *
 * @package    Anketa
 * @subpackage Anketa
 * @author     Peter Perešíni <ppershing@gmail.com>
 */

namespace AnketaBundle\Tests\Lib;

use PHPUnit_Framework_TestCase;
use AnketaBundle\Lib\StatisticalFunctions;

class StatisticalFunctionsTest extends PHPUnit_Framework_TestCase
{
    public function testCountSimple() {
        $data = array(0.86, 1.53, 1.57, 1.81, 0.99, 1.09, 1.29, 1.78, 1.29, 1.58);
        $this->assertEquals(10, StatisticalFunctions::cnt($data));
    }

    public function testAverage() {
        $data = array(0.86, 1.53, 1.57, 1.81, 0.99, 1.09, 1.29, 1.78, 1.29, 1.58);
        $this->assertEquals(1.379, StatisticalFunctions::average($data),
                '', 0.001);
    }

    public function testMedianOdd() {
        $data = array(0.86, 1.53, 1.57, 1.81, 0.99, 1.09, 1.29, 1.78, 1.29);
        $this->assertEquals(1.29, StatisticalFunctions::median($data),
                '', 0.001);
    }

    public function testMedianOddMore() {
        $data = array(0.86, 1.53, 1.57, 1.81, 0.99, 1.09, array(1.29, 2), 1.78);
        $this->assertEquals(1.29, StatisticalFunctions::median($data),
                '', 0.001);
    }

    public function testMedianEvenMore() {
        $data = array(0.86, 1.53, 1.57, 1.81, 0.99, 1.09, array(1.29, 2), 1.78, 1.58);
        $this->assertEquals(1.41, StatisticalFunctions::median($data),
                '', 0.001);
    }

    public function testMedianEvenInside() {
        $data = array(array(2.56, 4), array(1.29, 6));
        $this->assertEquals(1.29, StatisticalFunctions::median($data),
                '', 0.001);
    }

    public function testMedian2() {
        $data = array(array(-1, 2), array(1, 3));
        $this->assertEquals(1, StatisticalFunctions::median($data),
                '', 0.001);
    }

    public function testMedian3() {
        $data = array(-1, -1, 1, 1, 1);
        $this->assertEquals(1, StatisticalFunctions::median($data),
                '', 0.001);
    }

    public function testAverageComplex() {
        $data = array(47,
                      array(-2, 5),
                      array(3, 2)
                );
        $this->assertEquals(43.0 / 8, StatisticalFunctions::average($data),
                '', 1e-10);
    }

    public function testStddev() {
        $data = array(0.86, 1.53, 1.57, 1.81, 0.99, 1.09, 1.29, 1.78, 1.29, 1.58);
        $this->assertEquals(0.327, StatisticalFunctions::stddev($data),
                '', 0.001);
    }

    public function testAverageNoPoint() {
        $this->setExpectedException("AnketaBundle\Lib\StatisticalException");
        StatisticalFunctions::average(array());
    }

    public function testMedianNoPoint() {
        $this->setExpectedException("AnketaBundle\Lib\StatisticalException");
        StatisticalFunctions::median(array());
    }

    public function testStddevOnePoint() {
        $this->setExpectedException("AnketaBundle\Lib\StatisticalException");
        StatisticalFunctions::stddev(array(1));
    }
}
