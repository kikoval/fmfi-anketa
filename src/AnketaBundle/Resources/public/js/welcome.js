
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */

jQuery(document).ready(function ($) {
  "use strict";

  var $wrapper = $('.progressbar-welcome-wrapper');
  /** @var double number of seconds to fill 100% progressbar */
  var maxTime = 10;
  if ($wrapper.length) {
    var targetWidth = $wrapper.attr('title');
    $wrapper.removeAttr('title');
    $wrapper.html('<div class="progressbar-welcome"><span class="done"></span><span class="text"></span></div>');
    $wrapper.find('.text').text(targetWidth);
    $wrapper.find('.done').animate({ width: targetWidth },
                                   maxTime * 10 * parseInt(targetWidth) );
  }
});

