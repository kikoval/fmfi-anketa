
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */

jQuery(document).ready(function ($) {
  "use strict";

  // fungujeme len v question controlleri
  if ($('#content .submit').length == 0) return;

  function getState() {
    var result = [];
    $('input, textarea, option').each(function () {
      result.push($(this).val());
      result.push(this.checked);
      result.push(this.selected);
    });
    return result.join("|");
  }
  var initialState = getState();

  var submitting = false;
  $('form').bind('submit', function () { submitting = true; });

  window.onbeforeunload = function (event) {
    event = event || window.event;

    if (submitting) return;
    if (getState() == initialState) return;

    var question = 'V niektorých odpovediach sú neuložené zmeny! Ak nestlačíte tlačidlo "Ulož", budú stratené. Naozaj chcete odísť?';

    if (event) event.returnValue = question;
    return question;
  };
});

