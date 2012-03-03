/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 */

jQuery(document).ready(function ($) {
  "use strict";

  var $subjects = $("ul.subject-listing li");
  if (!$subjects.length) return;

  var oldQuery = '';
  function search(query) {
    if (query == oldQuery) return;
    oldQuery = query;

    var words = [];
    $.each(query.toLowerCase().split('"'), function (index, chunk) {
      if (index % 2) {   // je to v uvodzovkach
        words.push(chunk);
      }
      else {   // je to mimo uvodzoviek, rozdelime to na slova
        var matches = chunk.match(/\S+/g) || [];
        for (var i = 0; i < matches.length; i++) words.push(matches[i]);
      }
    });

    $subjects.each(function () {
      var text = $(this).text().toLowerCase();
      for (var i = 0; i < words.length; i++) {
        if (text.indexOf(words[i]) == -1) {
          $(this).hide();
          return;
        }
      }
      $(this).show();
    });

    $('#content h3').each(function () {
      $(this).toggle($(this).next().find('li:visible').length > 0);
    });
  }

  $("#content").prepend('<div id="subject-filter-wrapper">Filtrovať predmety: <input type="text" id="subject-filter"></div>');
  var $input = $('#subject-filter');
  $input.bind('keydown keyup input', function () {
    setTimeout(function () { search($input.val()); }, 0);
  });
});

