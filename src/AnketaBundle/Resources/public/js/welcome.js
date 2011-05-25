
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
  var maxTimeAnon = 8;
  var maxTimeVoters = 6.4;
  if ($wrapper.length) {
    var progressAnon = $wrapper.attr('data-anon');
    var progressVoters = $wrapper.attr('data-voters');
    // .voters span is first in DOM to be lower in z-index than .anon
    $wrapper.html('<div class="progressbar-welcome"><span class="voters"></span><span class="anon"></span><span class="text"></span></div>');
    $wrapper.find('.text').text(progressAnon);
    $wrapper.find('.voters').animate({ width: progressVoters },
                                   maxTimeVoters * 10 * parseInt(progressVoters) );
    $wrapper.find('.anon').animate({ width: progressAnon },
                                   maxTimeAnon * 10 * parseInt(progressAnon) );    
  }
});

