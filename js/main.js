// =============================================
// ESSENTIAL THEME FUNCTIONS (from your old main.js)
// =============================================

(function($){ "use strict";
             
    $(window).on('load', function() {
        $('body').addClass('loaded');
    });
             
/*=========================================================================
	Header Sticky Functionality
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
	Main Slider - KEEP THIS EXACTLY AS IS
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

/*=========================================================================
    Carousels - KEEP THESE
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
	Scroll and Animation Functions
=========================================================================*/
	smoothScroll.init({ offset: 60 });
	 
    // Scroll To Top
    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 100) {
            $('#scroll-to-top').fadeIn();
        } else {
            $('#scroll-to-top').fadeOut();
        }
    });

    // WOW Animations
    new WOW().init();

    // Venobox for image popups
    $('.img-popup').venobox({
        numeratio: true,
        infinigall: true
    });

/*=========================================================================
    Mailchimp (Keep if needed)
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

})(jQuery);

// =============================================
// OUR CLEAN UI FUNCTIONS (from our new code)
// =============================================

// Modal Management Functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

function initializeModalListeners() {
    // Close modals when clicking X
    const closeButtons = document.querySelectorAll('.land-modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.land-modal');
            closeModal(modal.id);
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
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    closeModal(modal.id);
                }
            });
        }
    });
}

// Tab Navigation Functions
function openTab(tabName) {
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

// Collapsible Section Functions
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
    } else {
        section.style.display = 'none';
    }
}

// Service Selection Functions
function initializeServiceSelection() {
    const serviceOptions = document.querySelectorAll('.service-option');
    const requestFormSection = document.getElementById('request-form-section');
    const selectedServiceInput = document.getElementById('selected-service'); // hidden input for service_id
    const selectedServiceTitle = document.getElementById('selected-service-title');
    const changeServiceBtn = document.getElementById('change-service-btn');
    
    if (serviceOptions.length > 0) {
        serviceOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Get ID and name from attributes
                const serviceId = this.getAttribute('data-id');
                const serviceName = this.getAttribute('data-name');

                // Reset all cards' borders and indicators
                serviceOptions.forEach(opt => {
                    opt.style.border = 'none';
                    const indicator = opt.querySelector('.service-select-indicator');
                    if (indicator) indicator.style.display = 'none';
                });

                // Highlight selected card
                this.style.border = '2px solid #ff7607';
                const indicator = this.querySelector('.service-select-indicator');
                if (indicator) indicator.style.display = 'block';

                // Set hidden input and update title
                if (selectedServiceInput) selectedServiceInput.value = serviceId;
                if (selectedServiceTitle) selectedServiceTitle.textContent = serviceName;

                // Show the request form
                if (requestFormSection) {
                    requestFormSection.style.display = 'block';
                    requestFormSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }
    
    // "Change Service" button logic
    if (changeServiceBtn && requestFormSection) {
        changeServiceBtn.addEventListener('click', function() {
            requestFormSection.style.display = 'none';
            serviceOptions.forEach(opt => {
                opt.style.border = 'none';
                const indicator = opt.querySelector('.service-select-indicator');
                if (indicator) indicator.style.display = 'none';
            });
            const serviceOptionsSection = document.getElementById('service-options');
            if (serviceOptionsSection) {
                serviceOptionsSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
}


// Success Message Functions
function showSuccess(message, details) {
    const successModal = document.getElementById('successModal');
    const successMessage = document.getElementById('successMessage');
    const successDetails = document.getElementById('successDetails');
    
    if (successMessage) successMessage.textContent = message;
    if (successDetails) successDetails.textContent = details;
    if (successModal) openModal('successModal');
}

// Job Application Functions
// Job Application Functions - REPLACE the existing initializeJobApplication function in main.js
function initializeJobApplication() {
    const applyButtons = document.querySelectorAll('.apply-now-btn');
    const jobTitleElement = document.getElementById('selected-job-title');
    const appliedPositionInput = document.getElementById('applied-position');
    const appliedJobIdInput = document.getElementById('applied-job-id');
    
    if (applyButtons.length > 0) {
        applyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const jobId = this.getAttribute('data-job-id');
                const jobTitle = this.getAttribute('data-job-title');
                
                // Set the job title display
                if (jobTitleElement) jobTitleElement.textContent = jobTitle;
                
                // Set hidden inputs
                if (appliedPositionInput) appliedPositionInput.value = jobTitle;
                if (appliedJobIdInput) appliedJobIdInput.value = jobId;
                
                // Open the modal
                openModal('job-application-modal');
            });
        });
    }
}

// Equipment Maintenance Functions
function requestMaintenance(equipmentName) {
    const maintenanceEquipment = document.getElementById('maintenanceEquipment');
    if (maintenanceEquipment) {
        maintenanceEquipment.value = equipmentName;
    }
    openModal('maintenanceRequestModal');
}



function resetServiceSelection() {
    const serviceOptions = document.querySelectorAll('.service-option');
    serviceOptions.forEach(opt => {
        opt.style.border = 'none';
        const indicator = opt.querySelector('.service-select-indicator');
        if (indicator) indicator.style.display = 'none';
    });
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeModalListeners();
    //initializeFormHandlers();
    initializeServiceSelection();
    initializeJobApplication();
    
    console.log('All UI components initialized successfully');
}


);
// =============================================
// LAND LISTING FUNCTIONALITY
// Add this section to your main.js file
// =============================================

// Configuration
const INITIAL_LANDS_TO_SHOW = 6;
let showingAll = false;
let selectedFiles = [];

// Initialize land listing features when DOM is ready
function initializeLandListingFeatures() {
    // Only run if we're on the lands page
    if (document.querySelector('.land-grid')) {
        initializeLandDisplay();
        initializeFilters();
        initializeLandModals();
        initializeUploadModal();
        initializePhotoUpload();
        autoHideAlerts();
    }
}

// Initialize land display with limit
function initializeLandDisplay() {
    const landItems = document.querySelectorAll('.land-item');
    
    landItems.forEach((item, index) => {
        if (index >= INITIAL_LANDS_TO_SHOW) {
            item.style.display = 'none';
            item.classList.add('hidden-land');
        }
    });
    
    updateViewAllButton();
}

// Update "View All Lands" button functionality
function updateViewAllButton() {
    const viewAllBtn = document.querySelector('.default-btn[href="#"]');
    const hiddenLands = document.querySelectorAll('.land-item.hidden-land');
    
    if (viewAllBtn) {
        if (hiddenLands.length === 0) {
            viewAllBtn.style.display = 'none';
        } else {
            viewAllBtn.style.display = 'inline-block';
            viewAllBtn.textContent = showingAll ? 'Show Less' : `View All Lands (${hiddenLands.length} more)`;
            
            // Remove old listeners and add new one
            const newBtn = viewAllBtn.cloneNode(true);
            viewAllBtn.parentNode.replaceChild(newBtn, viewAllBtn);
            
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleShowAllLands();
            });
        }
    }
}

// Toggle show all lands
function toggleShowAllLands() {
    const landItems = document.querySelectorAll('.land-item');
    
    if (showingAll) {
        // Show only first 6
        landItems.forEach((item, index) => {
            if (index >= INITIAL_LANDS_TO_SHOW) {
                item.style.display = 'none';
                item.classList.add('hidden-land');
            }
        });
        showingAll = false;
        // Scroll to lands section
        const projectsSection = document.querySelector('.projects-section');
        if (projectsSection) {
            projectsSection.scrollIntoView({ behavior: 'smooth' });
        }
    } else {
        // Show all lands
        landItems.forEach(item => {
            item.style.display = 'block';
            item.classList.remove('hidden-land');
        });
        showingAll = true;
    }
    
    updateViewAllButton();
}

// Initialize filter functionality
function initializeFilters() {
    const propertyTypeFilter = document.getElementById('property-type');
    const locationFilter = document.getElementById('location');
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');
    const applyBtn = document.querySelector('.filter-btn');
    const resetBtn = document.querySelector('.reset-btn');
    
    if (!applyBtn || !resetBtn) return;
    
    // Apply filters button
    applyBtn.addEventListener('click', function() {
        applyFilters();
    });
    
    // Reset filters button
    resetBtn.addEventListener('click', function() {
        resetFilters();
    });
    
    // Allow Enter key to apply filters
    if (minPriceInput && maxPriceInput) {
        [minPriceInput, maxPriceInput].forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });
        });
    }
}

// Apply filters function
function applyFilters() {
    const propertyTypeEl = document.getElementById('property-type');
    const locationEl = document.getElementById('location');
    const minPriceEl = document.getElementById('min-price');
    const maxPriceEl = document.getElementById('max-price');
    
    if (!propertyTypeEl || !locationEl || !minPriceEl || !maxPriceEl) return;
    
    const propertyType = propertyTypeEl.value.toLowerCase();
    const location = locationEl.value.toLowerCase();
    const minPrice = parseFloat(minPriceEl.value) || 0;
    const maxPrice = parseFloat(maxPriceEl.value) || Infinity;
    
    const landItems = document.querySelectorAll('.land-item');
    let visibleCount = 0;
    let hiddenCount = 0;
    
    landItems.forEach((item, index) => {
        const itemType = item.getAttribute('data-type').toLowerCase();
        const itemLocation = item.getAttribute('data-location').toLowerCase();
        const itemPrice = parseFloat(item.getAttribute('data-price'));
        
        let matchesType = !propertyType || itemType === propertyType;
        let matchesLocation = !location || itemLocation.includes(location);
        let matchesPrice = itemPrice >= minPrice && itemPrice <= maxPrice;
        
        if (matchesType && matchesLocation && matchesPrice) {
            item.style.display = 'block';
            item.classList.remove('hidden-land');
            item.classList.remove('filtered-out');
            
            // Apply initial limit if not showing all
            if (!showingAll && visibleCount >= INITIAL_LANDS_TO_SHOW) {
                item.style.display = 'none';
                item.classList.add('hidden-land');
                hiddenCount++;
            } else {
                visibleCount++;
            }
        } else {
            item.style.display = 'none';
            item.classList.add('filtered-out');
        }
    });
    
    // Show message if no results
    showNoResultsMessage(visibleCount === 0 && hiddenCount === 0);
    
    // Update view all button
    updateViewAllButton();
    
    // Scroll to results
    const projectsSection = document.querySelector('.projects-section');
    if (projectsSection) {
        projectsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Reset filters function
function resetFilters() {
    const propertyTypeEl = document.getElementById('property-type');
    const locationEl = document.getElementById('location');
    const minPriceEl = document.getElementById('min-price');
    const maxPriceEl = document.getElementById('max-price');
    
    if (propertyTypeEl) propertyTypeEl.value = '';
    if (locationEl) locationEl.value = '';
    if (minPriceEl) minPriceEl.value = '';
    if (maxPriceEl) maxPriceEl.value = '';
    
    const landItems = document.querySelectorAll('.land-item');
    
    landItems.forEach((item, index) => {
        item.classList.remove('filtered-out');
        
        if (index < INITIAL_LANDS_TO_SHOW) {
            item.style.display = 'block';
            item.classList.remove('hidden-land');
        } else {
            item.style.display = 'none';
            item.classList.add('hidden-land');
        }
    });
    
    showingAll = false;
    showNoResultsMessage(false);
    updateViewAllButton();
}

// Show/hide no results message
function showNoResultsMessage(show) {
    let noResultsMsg = document.getElementById('no-results-message');
    const landGrid = document.querySelector('.land-grid');
    
    if (!landGrid) return;
    
    if (show) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.id = 'no-results-message';
            noResultsMsg.style.cssText = 'text-align: center; padding: 40px 20px; font-size: 18px; color: #666; width: 100%;';
            noResultsMsg.innerHTML = `
                <i class="fas fa-search" style="font-size: 48px; color: #ddd; margin-bottom: 15px; display: block;"></i>
                <p style="margin: 0;">No lands match your search criteria.</p>
                <p style="margin: 10px 0 0 0; font-size: 14px; color: #999;">Try adjusting your filters to see more results.</p>
            `;
            landGrid.appendChild(noResultsMsg);
        }
        noResultsMsg.style.display = 'block';
    } else {
        if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }
}

// Initialize upload modal
function initializeUploadModal() {
    const openBtn = document.getElementById('open-upload-modal');
    const closeBtn = document.getElementById('close-upload-modal');
    const cancelBtn = document.getElementById('cancel-upload');
    const modal = document.getElementById('upload-land-modal');
    
    if (openBtn && modal) {
        openBtn.addEventListener('click', function() {
            modal.style.display = 'flex';
        });
    }
    
    if (closeBtn && modal) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    if (cancelBtn && modal) {
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    if (modal) {
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
}

// Initialize photo upload functionality
function initializePhotoUpload() {
    const photosInput = document.getElementById('land-photos-input');
    const uploadArea = document.getElementById('upload-area');
    const photosPreview = document.getElementById('photos-preview');
    const photosCount = document.getElementById('photos-count');
    const thumbnailsContainer = document.getElementById('thumbnails-container');
    const clearAllBtn = document.getElementById('clear-all-photos');
    const uploadForm = document.getElementById('upload-land-form');
    
    if (!photosInput || !uploadArea) return;
    
    uploadArea.addEventListener('click', function() {
        photosInput.click();
    });
    
    photosInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        if (files.length === 0) return;
        
        // Validate files
        let validFiles = [];
        for (let file of files) {
            if (file.size > 5 * 1024 * 1024) {
                alert(`File "${file.name}" is larger than 5MB and will be skipped`);
                continue;
            }
            
            if (!file.type.match('image.*')) {
                alert(`File "${file.name}" is not an image and will be skipped`);
                continue;
            }
            
            validFiles.push(file);
        }
        
        if (validFiles.length === 0) return;
        
        // Add to selected files
        selectedFiles = [...selectedFiles, ...validFiles];
        
        // Update display
        updatePhotosDisplay();
    });
    
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            selectedFiles = [];
            photosInput.value = '';
            updatePhotosDisplay();
        });
    }
    
    // Hover effects
    uploadArea.addEventListener('mouseenter', function() {
        this.style.backgroundColor = '#f0f0f0';
    });
    
    uploadArea.addEventListener('mouseleave', function() {
        this.style.backgroundColor = '#f8f9fa';
    });
    
    // Form submission validation
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            if (selectedFiles.length === 0) {
                e.preventDefault();
                alert('Please upload at least one photo of your land');
                return false;
            }
        });
    }
}

function updatePhotosDisplay() {
    const uploadArea = document.getElementById('upload-area');
    const photosPreview = document.getElementById('photos-preview');
    const photosCount = document.getElementById('photos-count');
    const thumbnailsContainer = document.getElementById('thumbnails-container');
    
    if (!uploadArea || !photosPreview) return;
    
    if (selectedFiles.length === 0) {
        uploadArea.style.display = 'block';
        photosPreview.style.display = 'none';
        return;
    }
    
    uploadArea.style.display = 'none';
    photosPreview.style.display = 'block';
    
    // Update count
    if (photosCount) {
        photosCount.textContent = `${selectedFiles.length} photo${selectedFiles.length > 1 ? 's' : ''} selected`;
    }
    
    // Clear thumbnails
    if (thumbnailsContainer) {
        thumbnailsContainer.innerHTML = '';
    }
    
    // Create thumbnails
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const thumbnailDiv = document.createElement('div');
            thumbnailDiv.style.cssText = 'position: relative; width: 100px; height: 100px;';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);';
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.innerHTML = '&times;';
            removeBtn.style.cssText = 'position: absolute; top: -5px; right: -5px; width: 25px; height: 25px; border-radius: 50%; background: #dc3545; color: white; border: none; cursor: pointer; font-size: 18px; line-height: 1; box-shadow: 0 2px 5px rgba(0,0,0,0.2);';
            removeBtn.addEventListener('click', function() {
                removePhoto(index);
            });
            
            thumbnailDiv.appendChild(img);
            thumbnailDiv.appendChild(removeBtn);
            if (thumbnailsContainer) {
                thumbnailsContainer.appendChild(thumbnailDiv);
            }
        };
        reader.readAsDataURL(file);
    });
    
    // Update the file input with current files
    updateFileInput();
}

function removePhoto(index) {
    selectedFiles.splice(index, 1);
    updatePhotosDisplay();
}

function updateFileInput() {
    const photosInput = document.getElementById('land-photos-input');
    if (!photosInput) return;
    
    // Create a new DataTransfer object to update the file input
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });
    photosInput.files = dataTransfer.files;
}

// Initialize land details modals
function initializeLandModals() {
    // Land details modal handlers
    const detailButtons = document.querySelectorAll('.land-details-btn');
    detailButtons.forEach(btn => {
        btn.addEventListener('click', e => {
            const id = btn.getAttribute('data-land');
            const modal = document.getElementById(`land-modal-${id}`);
            if (modal) modal.style.display = 'flex';
        });
    });
    
    // Close buttons
    const closeButtons = document.querySelectorAll('.land-modal-close');
    closeButtons.forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            closeBtn.closest('.land-modal').style.display = 'none';
        });
    });
    
    // Click outside to close
    window.addEventListener('click', e => {
        if (e.target.classList.contains('land-modal')) {
            e.target.style.display = 'none';
        }
    });
}

// Auto-hide alert messages
function autoHideAlerts() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
}

// Add to the main DOMContentLoaded event
document.addEventListener('DOMContentLoaded', function() {
    // Call existing initialization functions
    initializeModalListeners();
    //initializeFormHandlers();
    initializeServiceSelection();
    initializeJobApplication();
    
    // Add land listing initialization
    initializeLandListingFeatures();
    
    console.log('All UI components initialized successfully');
});