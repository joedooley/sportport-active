jQuery(function ($) {

  function positionProduct (e) {

    let winHeight = window.innerHeight;

    let wpAdminBar = $('#wpadminbar').outerHeight() || 0;
    let beforeHeader = $('.before-header').outerHeight() || 0;
    let headerHeight = $('.site-header').outerHeight();
    let wcMessage = $('.woocommerce-message').outerHeight() || 0;

    let totalHeaderHeights = wpAdminBar + beforeHeader + headerHeight + wcMessage

    let headerTop = $('.site-header .title-area .site-title img').position().top;

    let footerTop = $('.above-footer-widgets').position().top;
    let paddingTop = parseInt($('.all-images').css('padding-top'));

    let scrollHeight = headerHeight + beforeHeader + beforeHeader + wcMessage + wpAdminBar;

    let leftHeight = winHeight - headerHeight - beforeHeader - wpAdminBar;

    let currScroll = $(window).scrollTop();

    let imageHeight = leftHeight - paddingTop;

    if (window.matchMedia("(min-width: 1024px)").matches) {

      //set left height yeah
      $('.all-images').height(leftHeight);

      //set image height
      $('.product-img-box').height(imageHeight);

      //check if fixed position or not
      if (footerTop < currScroll + winHeight - scrollHeight) {
        $('.all-images').css({
          'position': 'absolute',
          'top': footerTop - leftHeight - scrollHeight - 15
        });
      } else {
        $('.all-images').css({
          'position': 'fixed',
          'top': scrollHeight > currScroll ? scrollHeight - currScroll : 0
        });
      }

    } else {

      //Remove styling for mobile
      $('.all-images').attr('style', '');
      $('.product-img-box').attr('style', '');

    }
  }

  $(window).on('resize', positionProduct);

  $(window).load(function () {
    setTimeout(positionProduct, 200);
  });

  $(window).on('scroll', function (e) {
    positionProduct(e);
  });

});



