/*Theme Scripts */

(function($){ "use strict";
             
    $(window).on('load', function() {
        $('body').addClass('loaded');
    });
             
/*=========================================================================
	Header
=========================================================================*/
    var primaryHeader = $('.primary-header'),
        headerClone = primaryHeader.clone();
    $('.header').after('<div class="sticky-header"></div>');
    $('.sticky-header').html(headerClone);
    var headerSelector = document.querySelector(".sticky-header");
    var headroom = new Headroom(headerSelector, {
        offset: 100
    });

    headroom.init();

    //if ($('.primary-header').length) {
    //    $('.header .primary-header .burger-menu').on("click", function () {
     //       $(this).toggleClass('menu-open');
     //       $('.header .header-menu-wrap').slideToggle(300);
     //   });
     //   $('.sticky-header .primary-header .burger-menu').on("click", function () {
    //        $(this).toggleClass('menu-open');
    //        $('.sticky-header .header-menu-wrap').slideToggle(300);
    //    });
   // }

  //  $('.header-menu-wrap ul li:has(ul)').each(function () {
  //      $(this).append('<span class="dropdown-plus"></span>');
  //      $(this).addClass('dropdown_menu');
  //  });

   // $('.header-menu-wrap .dropdown-plus').on("click", function () {
   //     $(this).prev('ul').slideToggle(300);
   //     $(this).toggleClass('dropdown-open');
  //  });
  //  $('.header-menu-wrap .dropdown_menu a').append('<span></span>');

    // Responsive Classes
    function responsiveClasses() {
        var body = $('body');
        if ($(window).width() < 992) {
            body.removeClass('viewport-lg');
            body.addClass('viewport-sm');
        } else {
            body.removeClass('viewport-sm');
            body.addClass('viewport-lg');
        }
    }

    // Transparent Header
    function transparentHeader(){
        var header = $('.header.header-three'),
            headerHeight = header.height(),
            pageHeader = $('.page-header');
            pageHeader.css('padding-top', headerHeight + 'px');
    }

    //responsiveClasses();
    $(window).on("resize", function () {
        responsiveClasses();
        transparentHeader();
    }).resize();

    // Odometer JS
    $('.odometer').waypoint(
        function() {
            var odo = $(".odometer");
            odo.each(function() {
                var countNumber = $(this).attr("data-count");
                $(this).html(countNumber);
            });
        },
        {
            offset: "80%",
            triggerOnce: true
        }
    );

/*=========================================================================
	Main Slider
=========================================================================*/ 
$(document).ready(function () {
    // Pre-load the slider images to prevent flash of old images
    var sliderImages = [
        'img/survey-team-working.jpg',
        'img/topo-mapping.jpg', 
        'img/construction-staking.jpg'
    ];
    
    // Preload images
    sliderImages.forEach(function(src) {
        var img = new Image();
        img.src = src;
    });

    // Initialize slider after a brief delay to ensure images are ready
    setTimeout(function() {
        $('#main-slider').slick({
            autoplay: true,
            autoplaySpeed: 5000, // Reduced from 10000 to 5000 for faster rotation
            dots: true,
            fade: true,
            pauseOnHover: false, // Added to ensure continuous rotation
            pauseOnFocus: false, // Added to ensure continuous rotation
            prevArrow: '<div class="slick-prev"><i class="fa fa-chevron-left"></i></div>',
            nextArrow: '<div class="slick-next"><i class="fa fa-chevron-right"></i></div>'
        });
    }, 100);

    // Animation functions (keep your existing code)
    $('#main-slider').on('init', function(e, slick) {
        var $firstAnimatingElements = $('div.single-slide:first-child').find('[data-animation]');
        doAnimations($firstAnimatingElements);    
    });
    
    $('#main-slider').on('beforeChange', function(e, slick, currentSlide, nextSlide) {
        var $animatingElements = $('div.single-slide[data-slick-index="' + nextSlide + '"]').find('[data-animation]');
        doAnimations($animatingElements);    
    });
    
    function doAnimations(elements) {
        var animationEndEvents = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        elements.each(function() {
            var $this = $(this);
            var $animationDelay = $this.data('delay');
            var $animationType = 'animated ' + $this.data('animation');
            $this.css({
                'animation-delay': $animationDelay,
                '-webkit-animation-delay': $animationDelay
            });
            $this.addClass($animationType).one(animationEndEvents, function() {
                $this.removeClass($animationType);
            });
        });
    }
});













   // $(document).ready(function () {

   //     $('#main-slider').on('init', function(e, slick) {
  //          var $firstAnimatingElements = $('div.single-slide:first-child').find('[data-animation]');
   //         doAnimations($firstAnimatingElements);    
  //      });
  //      $('#main-slider').on('beforeChange', function(e, slick, currentSlide, nextSlide) {
   //               var $animatingElements = $('div.single-slide[data-slick-index="' + nextSlide + '"]').find('[data-animation]');
    //              doAnimations($animatingElements);    
   //     });
    //    $('#main-slider').slick({
   //        autoplay: true,
    //       autoplaySpeed: 10000,
    //       dots: true,
     //      fade: true,
     //      prevArrow: '<div class="slick-prev"><i class="fa fa-chevron-left"></i></div>',
      //          nextArrow: '<div class="slick-next"><i class="fa fa-chevron-right"></i></div>',
     //           lazyLoad: 'progressive'
    //    });

   //     setTimeout(function() {
 //   $('.single-slide:nth-child(1) .bg-img').css('background-image', 'url(img/survey-team-working.jpg)');
 //   $('.single-slide:nth-child(2) .bg-img').css('background-image', 'url(img/topo-mapping.jpg)');
 //   $('.single-slide:nth-child(3) .bg-img').css('background-image', 'url(img/construction-staking.jpg)');
//}, 1000);


     //   function doAnimations(elements) {
        //    var animationEndEvents = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        //    elements.each(function() {
       //         var $this = $(this);
       //         var $animationDelay = $this.data('delay');
        //        var $animationType = 'animated ' + $this.data('animation');
       //         $this.css({
          //          'animation-delay': $animationDelay,
       ///             '-webkit-animation-delay': $animationDelay
      ///          });
      //          $this.addClass($animationType).one(animationEndEvents, function() {
      //              $this.removeClass($animationType);
      //          });
      //      });
    //    }
   // });
             
/*=========================================================================
    Service Carousel
=========================================================================*/
    $('#service-carousel').owlCarousel({
        loop: true,
        margin: 0,
        autoplay: false,
        smartSpeed: 800,
        nav: true,
        navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
        dots: false,
        responsive : {
            0 : {
                items: 1
            },
            480 : {
                items: 1,
            },
            768 : {
                items: 2,
            },
            992 : {
                items: 4,
            }
        }
    });

/*=========================================================================
    Projects Carousel
=========================================================================*/
    $('#projects-carousel').owlCarousel({
        loop: true,
        margin: 0,
        autoplay: false,
        smartSpeed: 500,
        nav: true,
        navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
        dots: false,
        responsive : {
            0 : {
                items: 1
            },
            580 : {
                items: 2,
            },
            768 : {
                items: 2,
            },
            992 : {
                items: 4,
            }
        }
    });

/*=========================================================================
    Project Single Carousel
=========================================================================*/
    $('#project-single-carousel').owlCarousel({
        loop: true,
        margin: 5,
        autoplay: true,
        smartSpeed: 500,
        nav: false,
        navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
        dots: true,
        responsive : {
            0 : {
                items: 1
            },
            480 : {
                items: 1,
            },
            768 : {
                items: 1,
            },
            992 : {
                items: 1,
            }
        }
    }); 

/*=========================================================================
    Testimonial Carousel
=========================================================================*/
    $('#testimonial-carousel').owlCarousel({
        loop: true,
        margin: 10,
        center: false,
        autoplay: true,
        smartSpeed: 500,
        nav: false,
        navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
        dots: true,
        responsive : {
            0 : {
                items: 1
            },
            480 : {
                items: 1,
            },
            768 : {
                items: 1,
            },
            992 : {
                items: 2,
            }
        }
    });

/*=========================================================================
    Sponsor Carousel
=========================================================================*/
    $('#sponsor-carousel').owlCarousel({
        loop: true,
        margin: 5,
        center: false,
        autoplay: true,
        smartSpeed: 500,
        nav: false,
        navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
        dots: false,
        responsive : {
            0 : {
                items: 2
            },
            480 : {
                items: 3,
            },
            768 : {
                items: 3,
            },
            992 : {
                items: 6,
            }
        }
    });
             
/*=========================================================================
	Initialize smoothscroll plugin
=========================================================================*/
	smoothScroll.init({
		offset: 60
	});
	 
/*=========================================================================
	Scroll To Top
=========================================================================*/ 
    $(window).on( 'scroll', function () {
        if ($(this).scrollTop() > 100) {
            $('#scroll-to-top').fadeIn();
        } else {
            $('#scroll-to-top').fadeOut();
        }
    });

/*=========================================================================
	WOW Active
=========================================================================*/ 
   new WOW().init();

/*=========================================================================
    Active venobox
=========================================================================*/
    $('.img-popup').venobox({
        numeratio: true,
        infinigall: true
    });
             
/*=========================================================================
	MAILCHIMP
=========================================================================*/ 

    if ($('.subscribe_form').length>0) {
        /*  MAILCHIMP  */
        $('.subscribe_form').ajaxChimp({
            language: 'es',
            callback: mailchimpCallback,
            url: "//alexatheme.us14.list-manage.com/subscribe/post?u=48e55a88ece7641124b31a029&amp;id=361ec5b369" 
        });
    }

    function mailchimpCallback(resp) {
        if (resp.result === 'success') {
            $('#subscribe-result').addClass('subs-result');
            $('.subscription-success').text(resp.msg).fadeIn();
            $('.subscription-error').fadeOut();

        } else if(resp.result === 'error') {
            $('#subscribe-result').addClass('subs-result');
            $('.subscription-error').text(resp.msg).fadeIn();
        }
    }
    $.ajaxChimp.translations.es = {
        'submit': 'Submitting...',
        0: 'We have sent you a confirmation email',
        1: 'Please enter your email',
        2: 'An email address must contain a single @',
        3: 'The domain portion of the email address is invalid (the portion after the @: )',
        4: 'The username portion of the email address is invalid (the portion before the @: )',
        5: 'This email address looks fake or invalid. Please enter a real email address'
    };

/*=========================================================================
    Google Map Settings
=========================================================================*/
    if($("body").hasClass("contact-page")){
        google.maps.event.addDomListener(window, 'load', init);

        function init() {

            var mapOptions = {
                zoom: 11,
                center: new google.maps.LatLng(40.6700, -73.9400), 
                scrollwheel: false,
                navigationControl: false,
                mapTypeControl: false,
                scaleControl: false,
                draggable: false,
                styles: [{"featureType":"administrative","elementType":"all","stylers":[{"saturation":"-100"}]},{"featureType":"administrative.province","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"all","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},{"featureType":"poi","elementType":"all","stylers":[{"saturation":-100},{"lightness":"50"},{"visibility":"simplified"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":"-100"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"lightness":"30"}]},{"featureType":"road.local","elementType":"all","stylers":[{"lightness":"40"}]},{"featureType":"transit","elementType":"all","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]},{"featureType":"water","elementType":"labels","stylers":[{"lightness":-25},{"saturation":-100}]}]
            };

            var mapElement = document.getElementById('google-map');

            var map = new google.maps.Map(mapElement, mapOptions);

            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(40.6700, -73.9400),
                map: map,
                title: 'Location!'
            });
        }
    }
    

    
/// land-sale button that open the details and different close techniques

// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get all detail buttons
    const detailButtons = document.querySelectorAll('.land-details-btn');
    const modals = document.querySelectorAll('.land-modal');
    const closeButtons = document.querySelectorAll('.land-modal-close');
    
    // Open modal when detail button is clicked
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const landId = this.getAttribute('data-land');
            const modal = document.getElementById(`land-modal-${landId}`);
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Close modal when close button is clicked
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.land-modal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Close modal with ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            modals.forEach(modal => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        }
    });
});
///////////////////
// Job Application Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const applyButtons = document.querySelectorAll('.apply-now-btn');
    const jobModal = document.getElementById('job-application-modal');
    const closeButton = document.querySelector('.land-modal-close');
    const selectedJobTitle = document.getElementById('selected-job-title');
    const appliedPosition = document.getElementById('applied-position');
    
    // Job titles mapping
    const jobTitles = {
        'project-manager': 'Project Manager',
        'site-engineer': 'Site Engineer', 
        'safety-officer': 'Safety Officer',
        'construction-worker': 'Construction Worker'
    };
    
    // Open modal when apply button is clicked
    applyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-job');
            const jobTitle = jobTitles[jobId];
            
            selectedJobTitle.textContent = jobTitle;
            appliedPosition.value = jobId;
            
            jobModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Close modal when close button is clicked
    closeButton.addEventListener('click', function() {
        jobModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === jobModal) {
            jobModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Close modal with ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            jobModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
});





})(jQuery);

/////////////////////////////////////////////////////////////////////////////////////////////////////



/*=========================================================================
    Common JavaScript Functions for All Pages
=========================================================================*/

(function($){ "use strict";

/*=========================================================================
    Preloader
=========================================================================*/
$(window).on('load', function() {
    $('body').addClass('loaded');
});

/*=========================================================================
    Modal Management (Common for all pages)
=========================================================================*/
window.openModal = function(modalId) {
    document.getElementById(modalId).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

window.closeModal = function(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Initialize modal close functionality
document.addEventListener('DOMContentLoaded', function() {
    // Close modals when clicking X
    const closeButtons = document.querySelectorAll('.land-modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.land-modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Close modals when clicking outside
    const modals = document.querySelectorAll('.land-modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Close modals with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    closeModal(modal.id);
                }
            });
        }
    });
});

/*=========================================================================
    Tab Navigation (Common pattern)
=========================================================================*/
window.openTab = function(tabName) {
    const tabcontent = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabcontent.length; i++) {
        tabcontent[i].classList.remove("active");
    }

    const tabbuttons = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < tabbuttons.length; i++) {
        tabbuttons[i].classList.remove("active");
    }

    document.getElementById(tabName).classList.add("active");
    event.currentTarget.classList.add("active");
}

/*=========================================================================
    Success Message Display (Common)
=========================================================================*/
window.showSuccess = function(message, details) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successDetails').textContent = details;
    openModal('successModal');
}

/*=========================================================================
    Header
=========================================================================*/
var primaryHeader = $('.primary-header'),
    headerClone = primaryHeader.clone();
$('.header').after('<div class="sticky-header"></div>');
$('.sticky-header').html(headerClone);
var headerSelector = document.querySelector(".sticky-header");
var headroom = new Headroom(headerSelector, {
    offset: 100
});
headroom.init();

// Responsive Classes
function responsiveClasses() {
    var body = $('body');
    if ($(window).width() < 992) {
        body.removeClass('viewport-lg');
        body.addClass('viewport-sm');
    } else {
        body.removeClass('viewport-sm');
        body.addClass('viewport-lg');
    }
}

// Transparent Header
function transparentHeader(){
    var header = $('.header.header-three'),
        headerHeight = header.height(),
        pageHeader = $('.page-header');
        pageHeader.css('padding-top', headerHeight + 'px');
}

$(window).on("resize", function () {
    responsiveClasses();
    transparentHeader();
}).resize();

/*=========================================================================
    Odometer JS
=========================================================================*/
$('.odometer').waypoint(
    function() {
        var odo = $(".odometer");
        odo.each(function() {
            var countNumber = $(this).attr("data-count");
            $(this).html(countNumber);
        });
    },
    {
        offset: "80%",
        triggerOnce: true
    }
);

/*=========================================================================
    Main Slider
=========================================================================*/ 
$(document).ready(function () {
    var sliderImages = [
        'img/survey-team-working.jpg',
        'img/topo-mapping.jpg', 
        'img/construction-staking.jpg'
    ];
    
    sliderImages.forEach(function(src) {
        var img = new Image();
        img.src = src;
    });

    setTimeout(function() {
        $('#main-slider').slick({
            autoplay: true,
            autoplaySpeed: 5000,
            dots: true,
            fade: true,
            pauseOnHover: false,
            pauseOnFocus: false,
            prevArrow: '<div class="slick-prev"><i class="fa fa-chevron-left"></i></div>',
            nextArrow: '<div class="slick-next"><i class="fa fa-chevron-right"></i></div>'
        });
    }, 100);

    $('#main-slider').on('init', function(e, slick) {
        var $firstAnimatingElements = $('div.single-slide:first-child').find('[data-animation]');
        doAnimations($firstAnimatingElements);    
    });
    
    $('#main-slider').on('beforeChange', function(e, slick, currentSlide, nextSlide) {
        var $animatingElements = $('div.single-slide[data-slick-index="' + nextSlide + '"]').find('[data-animation]');
        doAnimations($animatingElements);    
    });
    
    function doAnimations(elements) {
        var animationEndEvents = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        elements.each(function() {
            var $this = $(this);
            var $animationDelay = $this.data('delay');
            var $animationType = 'animated ' + $this.data('animation');
            $this.css({
                'animation-delay': $animationDelay,
                '-webkit-animation-delay': $animationDelay
            });
            $this.addClass($animationType).one(animationEndEvents, function() {
                $this.removeClass($animationType);
            });
        });
    }
});

/*=========================================================================
    Service Carousel
=========================================================================*/
$('#service-carousel').owlCarousel({
    loop: true,
    margin: 0,
    autoplay: false,
    smartSpeed: 800,
    nav: true,
    navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
    dots: false,
    responsive : {
        0 : { items: 1 },
        480 : { items: 1 },
        768 : { items: 2 },
        992 : { items: 4 }
    }
});

/*=========================================================================
    Projects Carousel
=========================================================================*/
$('#projects-carousel').owlCarousel({
    loop: true,
    margin: 0,
    autoplay: false,
    smartSpeed: 500,
    nav: true,
    navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
    dots: false,
    responsive : {
        0 : { items: 1 },
        580 : { items: 2 },
        768 : { items: 2 },
        992 : { items: 4 }
    }
});

/*=========================================================================
    Project Single Carousel
=========================================================================*/
$('#project-single-carousel').owlCarousel({
    loop: true,
    margin: 5,
    autoplay: true,
    smartSpeed: 500,
    nav: false,
    navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
    dots: true,
    responsive : {
        0 : { items: 1 },
        480 : { items: 1 },
        768 : { items: 1 },
        992 : { items: 1 }
    }
}); 

/*=========================================================================
    Testimonial Carousel
=========================================================================*/
$('#testimonial-carousel').owlCarousel({
    loop: true,
    margin: 10,
    center: false,
    autoplay: true,
    smartSpeed: 500,
    nav: false,
    navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
    dots: true,
    responsive : {
        0 : { items: 1 },
        480 : { items: 1 },
        768 : { items: 1 },
        992 : { items: 2 }
    }
});

/*=========================================================================
    Sponsor Carousel
=========================================================================*/
$('#sponsor-carousel').owlCarousel({
    loop: true,
    margin: 5,
    center: false,
    autoplay: true,
    smartSpeed: 500,
    nav: false,
    navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
    dots: false,
    responsive : {
        0 : { items: 2 },
        480 : { items: 3 },
        768 : { items: 3 },
        992 : { items: 6 }
    }
});
             
/*=========================================================================
    Initialize smoothscroll plugin
=========================================================================*/
smoothScroll.init({
    offset: 60
});
     
/*=========================================================================
    Scroll To Top
=========================================================================*/ 
$(window).on('scroll', function () {
    if ($(this).scrollTop() > 100) {
        $('#scroll-to-top').fadeIn();
    } else {
        $('#scroll-to-top').fadeOut();
    }
});

/*=========================================================================
    WOW Active
=========================================================================*/ 
new WOW().init();

/*=========================================================================
    Active venobox
=========================================================================*/
$('.img-popup').venobox({
    numeratio: true,
    infinigall: true
});
             
/*=========================================================================
    MAILCHIMP
=========================================================================*/ 
if ($('.subscribe_form').length>0) {
    $('.subscribe_form').ajaxChimp({
        language: 'es',
        callback: mailchimpCallback,
        url: "//alexatheme.us14.list-manage.com/subscribe/post?u=48e55a88ece7641124b31a029&amp;id=361ec5b369" 
    });
}

function mailchimpCallback(resp) {
    if (resp.result === 'success') {
        $('#subscribe-result').addClass('subs-result');
        $('.subscription-success').text(resp.msg).fadeIn();
        $('.subscription-error').fadeOut();
    } else if(resp.result === 'error') {
        $('#subscribe-result').addClass('subs-result');
        $('.subscription-error').text(resp.msg).fadeIn();
    }
}

$.ajaxChimp.translations.es = {
    'submit': 'Submitting...',
    0: 'We have sent you a confirmation email',
    1: 'Please enter your email',
    2: 'An email address must contain a single @',
    3: 'The domain portion of the email address is invalid (the portion after the @: )',
    4: 'The username portion of the email address is invalid (the portion before the @: )',
    5: 'This email address looks fake or invalid. Please enter a real email address'
};

/*=========================================================================
    Google Map Settings
=========================================================================*/
if($("body").hasClass("contact-page")){
    google.maps.event.addDomListener(window, 'load', init);

    function init() {
        var mapOptions = {
            zoom: 11,
            center: new google.maps.LatLng(40.6700, -73.9400), 
            scrollwheel: false,
            navigationControl: false,
            mapTypeControl: false,
            scaleControl: false,
            draggable: false,
            styles: [{"featureType":"administrative","elementType":"all","stylers":[{"saturation":"-100"}]},{"featureType":"administrative.province","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"all","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},{"featureType":"poi","elementType":"all","stylers":[{"saturation":-100},{"lightness":"50"},{"visibility":"simplified"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":"-100"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"lightness":"30"}]},{"featureType":"road.local","elementType":"all","stylers":[{"lightness":"40"}]},{"featureType":"transit","elementType":"all","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]},{"featureType":"water","elementType":"labels","stylers":[{"lightness":-25},{"saturation":-100}]}]
        };

        var mapElement = document.getElementById('google-map');
        var map = new google.maps.Map(mapElement, mapOptions);
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(40.6700, -73.9400),
            map: map,
            title: 'Location!'
        });
    }
}

})(jQuery);


