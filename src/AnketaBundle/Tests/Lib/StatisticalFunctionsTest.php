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
    public function testConfidenceC() {
        // cdf_t(deg=9, -inf, c) = 0.975 => c = 2.262
        $this->assertEquals(2.262,
                StatisticalFunctions::_confidenceC(9, 0.975),
                '', 0.001);
    }

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

    public function testConfidenceHalf() {
        $data = array(0.86, 1.53, 1.57, 1.81, 0.99, 1.09, 1.29, 1.78, 1.29, 1.58);
        // We compute the estimates
        $this->assertEquals(0.2343, StatisticalFunctions::confidenceHalf($data, 0.95),
                '', 0.0001);
        // u = 1.379, 1.1446 < u < 1.6134 (90% confidence)

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
