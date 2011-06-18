<?php
/**
 * This file contains functions computing some statistics over data
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

use fajr\libfajr\base\Preconditions;

/**
 * Compute statistics over data
 */
class StatisticalFunctions {

    /**
     * Checks validity and normalize data
     *
     * @param array $data array whose items may be
     *   $value or array($value, $count)
     *
     * Warning: we do not "group" the same values, so for $data = array(1, 1, 1)
     * the output is array(3 x array(1, 1))
     *
     * @returns array(array(value,count))
     */
    private static function checkAndNormalizeData(array $data) {
        $result = array();
        foreach ($data as $item) {
            if (is_array($item)) {
                Preconditions::check(array_keys($item) == array(0, 1));
                Preconditions::check(is_numeric($item[0]));
                Preconditions::check(is_int($item[1]));
                Preconditions::check($item[1] >= 0);
                if ($item[1] > 0) {
                    $result[] = $item;
                }
            } else {
                Preconditions::check(is_numeric($item));
                $result[] = array($item, 1);
            }
        }
        return $result;
    }

    /**
     * Count number of data points
     *
     * @param array $data @see checkAndNormalizeData for details
     *
     * @returns int count
     */
    public static function cnt(array $data) {
        $data = self::checkAndNormalizeData($data);
        return array_sum(array_map(function ($x) {return $x[1];}, $data));
    }

    /**
     * Return average value of data points
     *
     * Average value is defined as sum/cnt
     *
     * @param array $data @see checkAndNormalizeData for details
     *
     * @returns double average value
     * @throws
     */
    public static function average(array $data) {
        $data = self::checkAndNormalizeData($data);
        $cnt = self::cnt($data);
        if ($cnt == 0) {
            throw new StatisticalException("No data to average.");
        }
        return array_sum(array_map(function ($x) {return $x[0] * $x[1];}, $data)) / $cnt;
    }

    /**
     * Returns *sample* standard deviation
     *
     * Sample standard deviation is defined as
     * sqrt(1/(N-1) sum (x - avg)^2)
     *
     * @returns *sample* standard deviation
     */
    public static function stddev(array $data) {
        $data = self::checkAndNormalizeData($data);
        $cnt = self::cnt($data);
        if ($cnt < 2) {
            throw new StatisticalException("Not enough data for stddev");
        }
        $avg = self::average($data);

        $sigmaFunct = function ($x) use ($avg) {
                return $x[1] * ($avg - $x[0]) * ($avg - $x[0]);
            };
        return sqrt(array_sum(array_map($sigmaFunct, $data)) / ($cnt - 1));
    }


    /**
     * Calculates $x$ such that cumulative distribution functon of Students-t
     * distribution with $deg degrees of freedom is equal to $confidence
     *
     * @param int $deg degrees of freedom
     * @param double $confidence (probability value between 0 and 1)
     *
     * @returns double $x such that cdf_t($deg, $x) == $confidence
     */
    public static function _confidenceC($deg, $confidence) {
        // Note: stats_cdf_t is black magic function, documentation is horrible
        // parameters guessed from http://people.sc.fsu.edu/~jburkardt/cpp_src/dcdflib/dcdflib.C
        // Note: we cannot use $which=2, because of long outstanding bug
        // http://pecl.php.net/bugs/bug.php?id=14909&edit=2
        // Personal note(ppershing): PHP is HELL
        $left = 0;
        $right = 1000;
        for ($i = 0; $i < 60; $i++) {
            $middle = ($left + $right) / 2.0;
            $res = stats_cdf_t($middle, $deg, 1);
            if (!is_numeric($res)) {
                throw new Exception("stats_cdf_t returned unexpected result");
            }
            if ($res > $confidence) {
                $right = $middle;
            } else {
                $left = $middle;
            }
        }
        return $left;
        //return stats_cdf_t($confidence, $deg, 2); 
    }

    /**
     * Calculates confidence interval of avg estimate.
     * Interval is (avg - half, avg + half).
     *
     * Estimate the confidence interval of average estimate
     * assuming that the data are sampled
     * from the gaussian distribution.
     * For more details see
     * http://ocw.mit.edu/courses/mathematics/18-443-statistics-for-applications-fall-2006/lecture-notes/lecture5.pdf
     * 
     * @param $confidence required confidence 
     * @returns one half of the confidence interval
     */
    public static function confidenceHalf(array $data, $confidence) {
        Preconditions::check(is_numeric($confidence));
        Preconditions::check($confidence > 0);
        Preconditions::check($confidence < 1);
        $cnt = self::cnt($data);
        if ($cnt < 2) {
            throw new StatisticalException("Not enough data for confidence estimate");
        }
        $avg = self::average($data);
        $sigma = self::stddev($data);
        // confidence 90% means we need 95% half-interval confidence
        $c_half = (1 + $confidence) / 2.0;
        return $sigma / sqrt($cnt) * self::_confidenceC($cnt - 1, $c_half);
    }
}
