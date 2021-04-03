var tour = {
    id: "welcome-tour-hopscotch",
    steps: [
    
      {
          title: "Waashero Settings",
          content: "<p>Here are some useful features like:</p><ul><li><b>Development mode</b></li><li><b>OPcache & Object-Cache</b></li><li><b>CDN</b></li><li><b>Backups</b></li></ul>.",
          target: "toplevel_page_waashero_main_menu",
          placement: "bottom",
          yOffset: -7,
          arrowOffset: 0
      }
    ],
    i18n: {
        doneBtn: "Got it",
        stepNums: ["!", "!", "!"]
    },
    onEnd: function () {

        var data = {
            'action': 'waashero_welcome_tour',
        };

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            async: true,
            data: data,
        });
    }
};


jQuery(window).load(function () {
    // Start the tour!
    hopscotch.startTour(tour);
});



