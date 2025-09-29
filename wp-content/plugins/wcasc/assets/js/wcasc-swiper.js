(function ($) {
  "use strict";

  function initSwiperReco() {
    if ($("#wcasc-recos-track.swiper-initialized").length) return;

    new Swiper("#wcasc-recos-track", {
      slidesPerView: 1,
      navigation: {
        nextEl: ".wcasc-recos-next",
        prevEl: ".wcasc-recos-prev",
      },
    });
  }
  initSwiperReco();
  $(document.body).on(
    "wc_fragments_loaded wc_fragments_refreshed wc_fragment_refreshed",
    initSwiperReco
  );
})(jQuery);