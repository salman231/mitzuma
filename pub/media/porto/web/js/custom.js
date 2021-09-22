 require([
      'jquery',
      'owl.carousel/owl.carousel.min'
    ], function ($) {
      var owl_8 = $("#banner-slider-demo-main").owlCarousel({
        items: 1,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        dots: true,
        navRewind: true,
        animateIn: 'fadeIn',
        animateOut: 'fadeOut',
        loop: true,
        nav: false,
        navText: ["<em class='porto-icon-chevron-left'></em>","<em class='porto-icon-chevron-right'></em>"]
      });
	  var owl_product_popular= $("#popular_product_main .filterproducts").owlCarousel({
        items: 4,
		responsive:{0:{items:2},640:{items:2},768:{items:3},992:{items:4},1200:{items:4}},
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        dots: true,
        navRewind: true,
        animateIn: 'fadeIn',
        animateOut: 'fadeOut',
        loop: true,
        nav: false,
        navText: ["<em class='porto-icon-left-open-big'></em>","<em class='porto-icon-right-open-big'></em>"]
      });
	  var men_product= $(".section_2 #men_product .filterproducts,.section_3 #women_product .filterproducts").owlCarousel({
        items: 2,
		responsive:{0:{items:2},640:{items:2},768:{items:3},992:{items:4},1200:{items:4}},
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        dots: true,
        navRewind: true,
        animateIn: 'fadeIn',
        animateOut: 'fadeOut',
        loop: true,
        nav: false,
        navText: ["<em class='porto-icon-left-open-big'></em>","<em class='porto-icon-right-open-big'></em>"]
      });
	/*   var owl_product_popular= $(".section_4 #featured_product .filterproducts").owlCarousel({
        items: 4,
		responsive:{0:{items:2},640:{items:2},768:{items:3},992:{items:4},1200:{items:4}},
        autoplay: true,
        autoplayTimeout: 5000,
		margin:30,
        autoplayHoverPause: true,
        dots: false,
        navRewind: true,
        animateIn: 'fadeIn',
        animateOut: 'fadeOut',
        loop: true,
        nav: true,
        navText: ["<em class='porto-icon-left-open-big'></em>","<em class='porto-icon-right-open-big'></em>"]
      }); */
	   $("#fashion_product .owl-carousel").owlCarousel({
        loop: true,
        margin: 10,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        navRewind: true,
        nav: false,
        dots: true,
        responsive: {
          0: {
            items:2
          },
          768: {
            items:3
          },
          992: {
            items:4
          },
          1200: {
            items:4
          }
        }
      });
    });
require([
        'jquery',
        'Smartwave_Megamenu/js/sw_megamenu'
    ], function ($) {
        $(".sw-megamenu").swMegamenu();
    });