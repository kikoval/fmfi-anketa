<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Controller
 * @author     Peter Perešíni <ppershing@gmail.com>
 */

namespace AnketaBundle\Controller;

use fajr\libfajr\base\Preconditions;

// TODO(ppershing): make things private
class MenuItem {
    public $title;
    public $href;
    public $children;
    public $active;

    /**
     * @var numeric progress so far for this item
     *
     * Note: does not consider children status!
     */
    private $progress_done;

    /**
     * @var numeric maximum possible progress for this item
     *
     * Note: does not consider children status!
     */
    private $progress_all;

    public function __construct($title, $link) {
        Preconditions::checkIsString($title);
        Preconditions::checkIsString($link);
        $this->title = $title;
        $this->href = $link;
        $this->children = array();
        $this->active = false;
        $this->progress_done = 0;
        $this->progress_all = 0;
    }

    /**
     * Sets the progress of the current item.
     * Note that this function sets only item's own progress,
     * the resulting progress is calculated
     * also based on children!
     */
    public function setProgress($done, $total) {
        Preconditions::checkIsNumber($done);
        Preconditions::checkIsNumber($total);
        Preconditions::check($done >= 0);
        Preconditions::check($total >= 0);
        $this->progress_done = $done;
        $this->progress_all = $total;
    }

    public function getProgressDone() {
        $done = $this->progress_done;
        foreach ($this->children as $item) {
            $done += $item->getProgressDone();
        }
        return $done;
    }

    public function getProgressTotal() {
        $total = $this->progress_all;
        foreach ($this->children as $item) {
            $total += $item->getProgressTotal();
        }
        return $total;
    }

    public function getPercentDone() {
        $done = $this->getProgressDone();
        $total = $this->getProgressTotal();
        if ($total == 0) {
            return null;
        } else {
            return 100 * $done / $total;
        }
    }
}

