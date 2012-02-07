
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */

(function ($) {
  "use strict";

  // TODO: niekedy checknut nakolko je toto cele pristupne so screen readrom.
  // (neviem najst ziaden sposob ako toto cele na screen readri disablovat,
  // ani ziaden sposob ako screen readru povedat ze "pozor, toto sa zmenilo"
  // takze zatial na to kaslem.)

  // schovame stats-details este kym sa stranka nacitava
  $('html').addClass('with-stats-js');

  $(document).ready(function () {
    $('.stats-details').each(function () {
      var $details = $(this);
      var cnt = $details.find('[data-cnt]').data('cnt');
      var avg = $details.find('[data-avg]').data('avg');

      var text = "Priemerná hodnota: " + avg +
        (cnt == 1 ? " (dokopy 1 hlas)" :
        cnt >= 2 && cnt <= 4 ? "(dokopy "+cnt+" hlasy)" :
        " (dokopy "+cnt+" hlasov)") + " ";

      var $p = $('<p />').text(text);
      $p.append($('<a href="#">Viac detailov</a>').click(function () {
        $p.hide();
        $details.show();
        return false;
      }));
      $details.append($('<a href="#">Menej detailov</a>').click(function () {
        $p.show();
        $details.hide();
        return false;
      }));
      $p.insertBefore($details);
    });
  });
})(jQuery);

