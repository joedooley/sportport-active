(function($){ 
  $(document).ready(function() {
    var elems = $('*[showhide], *[onclick*=wp_showhide]'),
      $elem, id, text, settings, visible;

    for (var i = 0; i < elems.length; i++) {
      $elem = elems.eq(i);
      if ($elem.attr('showhide')) {
        id = "#" + $elem.attr("showhide");
        visible = !!$elem.attr("showhide_visible");
      } else {
        // legacy parse attrs
        id = "#" + $elem.attr('onclick').replace(/wp_showhide\.main|this|,| |\'|\(|\)|\"/g, "");
        $elem.attr('onclick', '');
      }

      text = $elem.text();

      // check if the element has text inside it and attempt to parse it
      if (text){
        settings = text.split(',');
        for (var j = 0; j < settings.length; j++){
          settings[j] = settings[j].trim();
        }
        $elem.data("wpsh_hide", settings[0]);
        $elem.data("wpsh_show", settings[1]);
        // legacy visibility
        if (settings.length > 2) {
          visible = (settings[2].toLowerCase() === 'visible') ? true : false;
        }
      }

      $elem.data("wpsh_id", id);

      if (visible) {
        if ($elem.data("wpsh_show")){
          $elem.text($elem.data("wpsh_show"));
        }
        $(id).show();
      }
      else {
        if ($elem.data("wpsh_hide")){
          $elem.text($elem.data("wpsh_hide"));
        }
        $(id).hide();
      }

      $elem.click(function(e) {
        var $elem = $(e.currentTarget);
        var id = $elem.data("wpsh_id");
        if ($(id).is(":visible")) {
          if ($elem.data("wpsh_hide")){
            $elem.text($elem.data("wpsh_hide"));
          }
          $(id).hide();
        }
        else {
          if ($elem.data("wpsh_show")){
            $elem.text($elem.data("wpsh_show"));
          }
          $(id).show();
        }
        return false;
      });
    }
  });
})(jQuery);
