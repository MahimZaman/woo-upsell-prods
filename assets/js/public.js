$ = jQuery;

$(document).ready(function () {
  const swiperContainer1 = document.querySelectorAll(".upsell-images");
  if (swiperContainer1.length > 0) {
    var swiper1 = new Swiper(".upsell-images", {
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    });
  }
  $(document)
    .on("change", ".upsell-item input", function () {
      const id = $(this).data("id");
      if ($(this).prop("checked")) {
        $(`#${id}`).prop("checked", true);
      } else {
        $(`#${id}`).prop("checked", false);
      }
    })
    .on("click", ".upsell-item img", function () {
      $(this).parent().find(".upsell-qv").fadeIn(200);
    })
    .on("click", ".close-upsell-qv", function (e) {
      e.stopPropagation();
      $(this).parent().fadeOut(200);
    })
    .on("click", function (e) {
      if (
        !$(e.target).closest(".upsell-qv").length &&
        !$(e.target).is(".upsell-item img")
      ) {
        $(".upsell-qv").fadeOut(100);
      }
    });
});
