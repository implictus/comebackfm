(function ($) {
  'use strict';
  $(function () {
    if ($.fn.wpColorPicker) {
      $('.pk-color').wpColorPicker();
    }
    $(document).on('click', '.pk-media-pick', function (e) {
      e.preventDefault();
      var $input = $(this).siblings('.pk-media-url').add($(this).prevAll('.pk-media-url')).first();
      if (!$input.length) $input = $(this).closest('td, p, div').find('.pk-media-url').first();
      var frame = wp.media({ title: 'Kies afbeelding', multiple: false });
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        $input.val(att.url).trigger('change');
      });
      frame.open();
    });
  });
})(jQuery);
