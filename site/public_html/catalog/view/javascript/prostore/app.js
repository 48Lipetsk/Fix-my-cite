function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
				}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();
			var html,i,value;
			var track = 0;

			$.extend(this, option);

			$(this).attr('autocomplete', 'off');

			// Focus
			$(this).on('focus', function() {
				this.request();
			});

			// Blur
			$(this).on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			// Keydown
			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			// Click
			this.click = function(event) { 
				event.preventDefault();

				value = $(event.target).closest('li').attr('data-value');

				if (value && this.items[value]) {
					if (this.items[value]['type'] == 'search') { 
						window.location = this.items[value]['value']; 
					}else{
						this.select(this.items[value]);						
					}
				}
				if (value == 'button') {window.location = $(event.target).attr('href');}
			}

			// Show
			this.show = function() {
				var pos = $(this).position();

				$(this).siblings('ul.dropdown-menu').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});

				$(this).siblings('ul.dropdown-menu').show();
			}

			// Hide
			this.hide = function() {
				$(this).siblings('ul.dropdown-menu').hide();
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			// Response
			this.response = function(json) { 

				if (json.length && json[0]['type'] == 'search') {
					html = '<ul class="header__search-menu">';

					if (json.length) {
						var short = 0;
						for (i = 0; i < json.length; i++) {
							this.items[json[i]['id']] = json[i];
						}

						for (i = 0; i < json.length; i++) {
							if (!json[i]['category']) {
								if (json[i]['image1']) { //console.log(json[i]['image1']);
									html += '<li data-value="' + json[i]['id'] +'"><a class="header__search-item" href="' + json[i]['value'] + '">';
									html += '<div class="header__search-item-image"><img src="' + json[i]['image1'] + '" alt="' + json[i]['label'] + '"></div>';
									html += '<div class="header__search-item-desc">';
									html += '<span class="header__search-item-title">' + json[i]['label'] + '</span>';
									if (json[i]['special']) { 
										html += '<span class="header__search-item-price is-xl-hidden"><ins>' + json[i]['special'] +'</ins><del>' + json[i]['price'] +'</del>';
										if (json[i]['sales'] && json[i]['discount']) { 
											html += '<mark>-' + json[i]['discount'] + '</mark>';
										}
										html += '</span>';
									}else{
										html += '<span class="header__search-item-price is-xl-hidden">' + json[i]['price'] +'</span>';
									}
									if (json[i]['model']) {
										html += '<span class="header__search-item-id">' + json[i]['model'] + '</span>';
									}
									html += '</div>';
									if (json[i]['special']) { 
										html += '<span class="header__search-item-price is-xl-visible"><ins>' + json[i]['special'] +'</ins><del>' + json[i]['price'] +'</del>';
										if (json[i]['sales'] && json[i]['discount']) { 
											html += '<mark>-' + json[i]['discount'] + '</mark>';
										}
										html += '</span>';
									}else{
										html += '<span class="header__search-item-price is-xl-visible">' + json[i]['price'] +'</span>';
										}
									html += '<span class="header__search-item-arrow is-xl-visible"><svg class="icon-arrow-search"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-arrow-search"></use></svg></span>';
									html += '</a>';
									
									html += '<div class="header__search-dropdown is-xl-visible"><div class="products__item"><div class="products__item-in">';
									html += '<div class="products__item-topleft"><div class="products__item-badges">';
									if (json[i]['isnewest']) {
										html += '<span class="ui-badge ui-badge--blue">' + json[i]['isnewest'] + '</span>';
									}
									if (json[i]['special'] && json[i]['sales'] != '') {
										html += '<span class="ui-badge ui-badge--red">'+ json[i]['sales'] +'</span>';
									}
									if (json[i]['popular']) {
										html += '<span class="ui-badge ui-badge--orange">' + json[i]['popular'] + '</span>';
									}
									if (json[i]['hit']) {
										html += '<span class="ui-badge ui-badge--purple">' + json[i]['hit'] + '</span>';
									}
									if (json[i]['nocatch']) {
										html += '<span class="ui-badge">' + json[i]['catch'] + '</span>';
									} else if (json[i]['catch']) {
										html += '<span class="ui-badge">' + json[i]['catch'] + '</span>';
									}
									if (json[i]['reward']) {
										html += '<span class="ui-badge ui-badge--transparent">' + json[i]['text_reward'] + ' ' + json[i]['reward'] + '</span>';
									}
									html += '</div>';
									if (json[i]['rating']) {
										html += '<span class="products__item-rating"><span class="sku__rating-star">' + json[i]['rating'] + '<svg class="icon-star"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-star"></use></svg></span></span>';
									}
									html += '</div>';
									html += '<div class="products__item-buttons">';
									if (json[i]['wish_compare_data']['compare_data']['is_in_compare']) {
										html += '<button type="button" class="ui-btn ui-btn--compare is-active" data-action="compare" data-for="' + json[i]['id'] +'"><svg class="icon-compare-active"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-compare-active"></use></svg></button>';
									}else{
										html += '<button type="button" class="ui-btn ui-btn--compare" data-action="compare" data-for="' + json[i]['id'] +'"><svg class="icon-compare"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-compare"></use></svg></button>';
									}
									if (json[i]['wish_compare_data']['wish_data']['is_in_wish']) {
										html += '<button type="button" class="ui-btn ui-btn--favorite is-active" data-action="wishlist" data-for="' + json[i]['id'] +'"><svg class="icon-favorites-active"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-favorites-active"></use></svg></button>';
									}else{
										html += '<button type="button" class="ui-btn ui-btn--favorite" data-action="wishlist" data-for="' + json[i]['id'] +'"><svg class="icon-favorites"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-favorites"></use></svg></button>';
									}
									html += '</div>';
									if (json[i]['images']) {
										html += '<a class="products__item-gallery" href="' + json[i]['value'] + '"><div class="products__item-image is-active"><img src="' + json[i]['image1'] + '" alt="' + json[i]['label'] + '" /></div>';
										for (k = 0; k < json[i]['images'].length; k++) {
											html += '<div class="products__item-image"><img data-src="' + json[i]['images'][k] + '" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" alt="' + json[i]['label'] + '" /></div>';
										}
										html += '<div class="products__item-pagination"><div class="products__item-bullet is-active"></div>';
										for (k = 0; k < json[i]['images'].length; k++) {
											html += '<div class="products__item-bullet"></div>';
										}
										html += '</div></a>';
									}else{
										html += '<a class="products__item-image" href="' + json[i]['value'] + '"><img src="' + json[i]['image1'] + '" alt="' + json[i]['label'] + '"/></a>';
									}
									if (json[i]['quantity'] > 0) {
										html += '<span class="products__item-status products__item-status--true">' + json[i]['stock'] + '</span>';
									}else{
										html += '<span class="products__item-status products__item-status--false">' + json[i]['stock'] + '</span>';
									}
									if (json[i]['manufacturer']) { 
										html += '<span class="products__item-id">' + json[i]['manufacturer'] + '</span>';
									}
									html += '<a class="products__item-title" href="' + json[i]['value'] + '">' + json[i]['label'] + '</a>';
									html += '<p class="products__item-price">';
									if (json[i]['special']) { 
										if (json[i]['sales'] && json[i]['discount']) { 
											html += '<mark>-' + json[i]['discount'] + '</mark>';
										}
										html += '<ins>' + json[i]['special'] + ' </ins><del>' + json[i]['price'] + '</del>';
									}else{
										html += json[i]['price'];
									}
									html += '</p>';
									if (json[i]['isincart']) { 
									html += '<div class="products__item-action"><div class="ui-add-to-cart is-active">';
									}else{
									html += '<div class="products__item-action"><div class="ui-add-to-cart">';
									}
										if (json[i]['isincart']) { 
										html += '<button type="button" class="ui-btn ui-btn--primary" onclick="window.location.href=\' ' + json[i]['cart_link'] + ' \';"' + (json[i]['buy_btn'] ? 'disabled' : '') + '>' + json[i]['button_to_cart'] + '<svg class="icon-cart"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-cart"></use></svg></button>';
										}else{
										html += '<button type="button" class="ui-btn ui-btn--primary" onclick="cart.add(\'' + json[i]['product_id'] + '\', \'' + json[i]['minimum'] +'\');"' + (json[i]['buy_btn'] ? 'disabled' : '') + '>' + json[i]['button_cart'] + '<svg class="icon-cart"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-cart"></use></svg></button>';
										}
										html += '<div class="ui-number"><button class="ui-number__decrease"><svg class="icon-decrease"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-decrease"></use></svg></button><button class="ui-number__increase"><svg class="icon-increase"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-increase"></use></svg></button><input class="ui-number__input" type="number" name="prod_id_quantity[' + json[i]['product_id'] + ']" value="' + json[i]['minimum'] + '" min="0" max="9999"></div>';
										html += '<a class="ui-btn ui-btn--view js-btn-preview" data-for="' + json[i]['product_id'] + '" href="#popupprod"><svg class="icon-view"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-view"></use></svg></a>';
									html += '</div></div>';
									
									html += '</div></div></div>';
									
									html += '</li>';
								}else if(json[i]['category_id']){
									html += '<li><a class="header__search-category" href="' + json[i]['value'] + '">';
									html += '<span class="header__search-category-icon">';
									html += '<svg class="icon-search"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-search"></use></svg>';
									html += '</span>';
									html += '<span class="header__search-category-title">' + json[i]['label'] + '</span>';
									html += '<span class="header__search-category-mark">' + json[i]['text_reward'] + '</span>';
									html += '</a></li>';								
								}else{
									html += '<li data-value="' + json[i]['value'] + '"><a href="#" class="search__item search__item--text">' + json[i]['label'] + '</a></li>';
								}

								if (json[i]['href']) {short = 1;}
							}
						}

						if (short) {
							html += '<li class="header__search-more"><a class="ui-link ui-link--blue" href="' + json[0]['href'] + '">' + json[0]['show_all'] + '</a></li>';
						}
						
						// Get all the ones with a categories
						var category = new Array();

						for (i = 0; i < json.length; i++) {
							if (json[i]['category']) {
								if (!category[json[i]['category']]) {
									category[json[i]['category']] = new Array();
									category[json[i]['category']]['name'] = json[i]['category'];
									category[json[i]['category']]['item'] = new Array();
								}

								category[json[i]['category']]['item'].push(json[i]);
							}
						}

						for (i in category) {
							html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

							for (j = 0; j < category[i]['item'].length; j++) {
								html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
							}
						}
						html += '</ul>';
					}

					if (html) {
						//this.show
						$('body').addClass('is-search-autocomplete');
					} else {
						//this.hide();
						$('body').removeClass('is-search-autocomplete');
					}

					$(this).parent().siblings('div.header__search-autocomplete').html(html);
					
					activateElements();
				}else{

					html = '';

					if (json.length) {
						for (i = 0; i < json.length; i++) {
							this.items[json[i]['value']] = json[i];
						}

						for (i = 0; i < json.length; i++) {
							if (!json[i]['category']) {
								html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
							}
						}

						// Get all the ones with a categories
						var category = new Array();

						for (i = 0; i < json.length; i++) {
							if (json[i]['category']) {
								if (!category[json[i]['category']]) {
									category[json[i]['category']] = new Array();
									category[json[i]['category']]['name'] = json[i]['category'];
									category[json[i]['category']]['item'] = new Array();
								}

								category[json[i]['category']]['item'].push(json[i]);
							}
						}

						for (i in category) {
							html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

							for (j = 0; j < category[i]['item'].length; j++) {
								html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
							}
						}
					}

					if (html) {
						this.show();
					} else {
						this.hide();
					}

					$(this).siblings('ul.dropdown-menu').html(html);

				}

			}

			if ($(this).hasClass('js-search-input')){

				//$(this).after('<div class="header__search-autocomplete"></div>');
				$(this).parent().siblings('div.header__search-autocomplete').on('click', 'ul.search__list > li > a' , $.proxy(this.click, this));

			}else{

				$(this).after('<ul class="dropdown-menu"></ul>');
				$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

			}				



		});
	}
})(window.jQuery);


// --------------------------------------------------------------------------
// Cookieagry
// --------------------------------------------------------------------------

function cookieagry() {
	$(document).on('click', '#cookieagry', function(e) {
		e.preventDefault();
		var date = new Date(new Date().getTime() + 1000 * 60 * 60 * 24 * 365);
		document.cookie = "cookieagry=1; path=/; expires=" + date.toUTCString();
		$('.cookieagry').hide();
	});
}

// --------------------------------------------------------------------------
// Scroll to top
// --------------------------------------------------------------------------

function scrollToTop() {
	var $s = $('.js-stt');
	if ($s.length && matchMedia('only screen and (min-width: 1199px)').matches) {
		$(window).scroll(function(){
			if ($(this).scrollTop() > 300){ 
				$s.addClass('active');
			} else {
				$s.removeClass('active');
			}
		});	
		$s.on('click', function(e){
			$('html, body').animate({
				scrollTop: 0
			}, 400);
			e.preventDefault();
		});
	}	
}


// --------------------------------------------------------------------------
// Switch language & currency
// --------------------------------------------------------------------------

function currlanguage() {
	// Currency
	$('body').on('click','#form-currency .header__currency-menu>li>a', function(e) {
		e.preventDefault();

		$('#form-currency input[name=\'code\']').val($(this).attr('data-curr'));

		$('#form-currency').submit();
	});

	// Language
	$('body').on('click','#form-language .header__language-menu>li>a', function(e) {
		e.preventDefault();

		$('#form-language input[name=\'code\']').val($(this).attr('data-lang'));

		$('#form-language').submit();
	});
}


// --------------------------------------------------------------------------
// callBack
// --------------------------------------------------------------------------
function callBack() {
	$(document).on('click','.contact-send',function() {
			var success = 'false';
			$.ajax({
				url: 'index.php?route=extension/module/callback',
				type: 'post',
				data: $(this).closest('.data-callback').serialize() + '&action=send',
				dataType: 'json',
				beforeSend: function() {
					$('.data-callback > button').attr('disabled', 'disabled');
				},
				complete: function() {
					$('.data-callback > button').removeAttr('disabled');
				},
				success: function(json) {
					$('.alert, .ui-error, .icon-error').remove();
					$('.ui-group, .ui-field').removeClass('is-error');
					
					if (json['warning']) {
						if (json['warning']['name']) {
							$('.data-callback input[name=\'name\']').after('<span class="error ui-error">' + json['warning']['name'] + '</span>').parent().addClass('is-error');
						}
						if (json['warning']['phone']) {
							$('.data-callback input[name=\'phone\']').after('<span class="error ui-error">' + json['warning']['phone'] + '</span>').parent().addClass('is-error');
						}
						if (json['warning']['captcha']) {	
							$('.data-callback input[name=\'captcha\'],.data-callback .js-captcha').after('<span class="error ui-error">' + json['warning']['captcha'] + '</span>').parent().addClass('is-error');
						}
					}
					if (json['success']){
						$('.popup__title').after('<div class="alert alert--green alert--opacity"><p class="alert__text">' + json['success'] + '</p></div>');
						
						success = 'true';
						
						sendMetrics('prostore_callback');
						
						$('.data-callback input,.data-callback textarea').val('');
						
						setTimeout(function(){
							$.fancybox.close();	
						}, 3000)
					} 
				}

			});
	});
}


// --------------------------------------------------------------------------
// getCompareWish
// --------------------------------------------------------------------------
function getCompareWish() {
	$('.js-compare-total').load('index.php?route=common/header/getcompare');			
	$('.js-wishlist-total').load('index.php?route=common/header/getwish');
}


// --------------------------------------------------------------------------
// Fix Event Listeners
// --------------------------------------------------------------------------

jQuery.event.special.touchstart = {
	setup: function( _, ns, handle ) {
		this.addEventListener("touchstart", handle, { passive: !ns.includes("noPreventDefault") });
	}
};
jQuery.event.special.touchmove = {
	setup: function( _, ns, handle ) {
		this.addEventListener("touchmove", handle, { passive: !ns.includes("noPreventDefault") });
	}
};
jQuery.event.special.wheel = {
	setup: function( _, ns, handle ){
		this.addEventListener("wheel", handle, { passive: true });
	}
};
jQuery.event.special.mousewheel = {
	setup: function( _, ns, handle ){
		this.addEventListener("mousewheel", handle, { passive: true });
	}
};


// --------------------------------------------------------------------------
// Nav
// --------------------------------------------------------------------------

function initPriorityNav() {
	if ( $('.header__priority').length ) {
		priorityNav.init({
			initClass:                  "is-header-priority", // Class that will be printed on html element to allow conditional css styling.
			mainNavWrapper:             ".header__priority", // mainnav wrapper selector (must be direct parent from mainNav)
			mainNav:                    "ul", // mainnav selector. (must be inline-block)
			navDropdownClassName:       "header__priority-dropdown", // class used for the dropdown - this is a class name, not a selector.
			navDropdownToggleClassName: "header__priority-toggle", // class used for the dropdown toggle - this is a class name, not a selector.
			navDropdownLabel:           ($('.header__priority').data('text-more') || 'Еще') + '<svg class="icon-arrow-down"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-arrow-down"></use></svg>', // Text that is used for the dropdown toggle.
			navDropdownBreakpointLabel: "menu", //button label for navDropdownToggle when the breakPoint is reached.
			breakPoint:                 0, //amount of pixels when all menu items should be moved to dropdown to simulate a mobile menu
			throttleDelay:              10, // this will throttle the calculating logic on resize because i'm a responsible dev.
			offsetPixels:               0, // increase to decrease the time it takes to move an item.
			count:                      true, // prints the amount of items are moved to the attribute data-count to style with css counter.
			//Callbacks
			moved: function () {
	
			},
			movedBack: function () {
				
			}
		});
	}

}





// --------------------------------------------------------------------------
// Header Sticky
// --------------------------------------------------------------------------

var c, currentScrollTop = 0;

function headerMobileSticky() {
	if ( $('body').is('.is-page-header-fixed, .is-page-sticky') ) {
		var a = $(window).scrollTop();
		var b = $('.header').innerHeight();

		if ( a > b ) {
			if ( $('.is-page-sticky').length ) {
				$('html').addClass('is-header-sticky');
			}
			if ( $('.is-page-header-fixed').length ) {
				$('html').addClass('is-header-fixed');
			}
			if ($('.header__catalog--clone').is(':empty') && matchMedia('only screen and (min-width: 1199px)').matches) { 
				$('.header__catalog.header__catalog--fullwidth').clone().prependTo('.header__catalog--clone');
			}
		} else {
			$('html').removeClass('is-header-fixed is-header-sticky is-header-sticky-open');
		}
		
		if ( $('.is-page-sticky').length ) {
			currentScrollTop = a;

			if (c < currentScrollTop && a > b + b) {
				$('html').removeClass('is-header-sticky-open');
			} else if (c > currentScrollTop && !(a <= b)) {
				$('html').addClass('is-header-sticky-open');
				
			}
		}
		c = currentScrollTop;
	}
}


$(window).on('scroll', function(event) {

	headerMobileSticky();
	
});

// --------------------------------------------------------------------------
// Header Nav
// --------------------------------------------------------------------------
 


var $headerHovers = '.header__catalog, .header__call, .header__currency, .header__language, .header__acc, .header__catalog li, .header__info li, .header__nav li, .header__nav .priority-nav__wrapper, .header__tags li, .footer__call';

$(document).on('mouseenter', $headerHovers, function(event) {
	if (matchMedia('only screen and (min-width: 1199px)').matches ) {
		$(this).addClass('is-open');
		$('body').removeClass('is-search-autocomplete');

		if ( $(this).closest('.header__nav, .header__tags') ) {
			var $offcanvas = $(this).find('.header__nav-offcanvas, .header__tags-offcanvas--fullwidth');

			$offcanvas.css('left', 0);

			if ($offcanvas.length) {
				var $offcanvas_offset = $offcanvas.offset().left;
				var $offcanvas_width = $offcanvas.innerWidth();
				var vw = $(window).innerWidth();

				var $offcanvas_position = vw - ($offcanvas_offset +  $offcanvas_width) - 25;

				if (  $offcanvas_position < 0) {
					$offcanvas.css('left', $offcanvas_position);
				}
				
			}
			
		}
	}
}).on('mouseleave', $headerHovers, function(event) {
	if (matchMedia('only screen and (min-width: 1199px)').matches ) {
		$(this).removeClass('is-open');
	}
});

$(document).on('mouseenter', '[data-catalog-target]', function(event) {
	if (matchMedia('only screen and (min-width: 1199px)').matches ) {
		$('[data-catalog-target], [data-catalog-dropdown]').removeClass('is-active');
		var target = $(this).data('catalog-target');
		if (target !== '') {
			$('[data-catalog-target='+ target +'], [data-catalog-dropdown='+ target +']').addClass('is-active');
		}
		
	}
});

$(document).on('mouseleave','.header__catalog', function(event) {
	$('[data-catalog-target], [data-catalog-dropdown]').removeClass('is-active')
});

	// Fixed


$(document).on('mouseenter', '.header-fixed .header__catalog-menu > li:has(.header__catalog-dropdown)', function(event) {

	var $li = $(this);
	var $liPositionTop = $li.position().top;

	var $dropdown = $li.children('.header__catalog-dropdown');


	var $dropdownHeight = $dropdown.children('.header__catalog-menu').innerHeight();


	// console.log($dropdownHeight + ' - ' + $(window).innerHeight() / 2 )

	if ( $dropdownHeight < $(window).innerHeight() / 1.5) {

		
		$dropdown.css({
			'top': $liPositionTop  + 'px',
			'transform': 'translateY(-1rem)'
		});


		var difference = $dropdown[0].getBoundingClientRect().bottom - $(window).innerHeight();


		if ( difference > 0 ) {
			$dropdown.css({
				'top': $liPositionTop - $dropdown.innerHeight() + $li.innerHeight()  + 'px',
				'transform': 'translateY(1rem)'
			});
		}

	}

});


// --------------------------------------------------------------------------
// Header Reverse Nav
// --------------------------------------------------------------------------

$(window).on('load resize', function(event) {


	$('.header__nav-dropdown').each(function( index ) {
		var fullWidth = $(window).innerWidth(),
			dropdown =  $(this),
			dropdownWidth = dropdown.innerWidth(),
			dropdownPosition = dropdown.offset().left,
			dropdownStatus = fullWidth - dropdownWidth - dropdownPosition;

			if(dropdownStatus < 0) {

				dropdown.addClass('is-reverse');
			}
			else {
				dropdown.removeClass('is-reverse');
			}

	});


});

// --------------------------------------------------------------------------
// Header Cart
// --------------------------------------------------------------------------

$(document).on('click', '.header__desktop .header__cart-btn, .header__cart-close, .header__cart-overlay', function(event) {
	event.preventDefault();
	
	if( $('html').is('.is-cart-open') ) {
		$('html').removeClass('is-cart-open');

	} else {
		$('html').addClass('is-cart-open');
	}
});



$(document).on('click', function (event) {
	if ( $(event.target).closest('.header__cart').length === 0) {
		$('html').removeClass('is-cart-open');
	}
});

function hasScrollBar() {
	
	// ----------
	$.fn.hasScrollBar = function() {
		return $(this).prop('scrollHeight') - $(this).innerHeight();
	}

	if ( $('.js-cart-scrollbar').hasScrollBar() ) {
		$('html').addClass('is-cart-sticky');
	}

	$('.js-cart-scrollbar').on('scroll', function(){
		var st = $(this).scrollTop();

		if ( st >= $(this).hasScrollBar() ) {
			$('html').removeClass('is-cart-sticky');
		} else {
			$('html').addClass('is-cart-sticky');
		}

	});

}


// --------------------------------------------------------------------------
// Header Search
// --------------------------------------------------------------------------

$(document).on('click', '.js-search-btn', function (event) {

	var url = $('base').attr('href') + 'index.php?route=product/search';
	
	var value = $(this).parent().find('input[name=\'search\']').val();

	if (value) {
		url += '&search=' + encodeURIComponent(value);
	}
	
	location = url;
});

$(document).on('keydown', '.js-search-input', function (event) {
	if (event.keyCode == 13) {
		$(this).parent().find('.js-search-btn').trigger('click');
	}
});
	
function SearchInput() {
	// $('.js-search-input').on('focus input', function(event) {

		// var searchLength = this.value.length;

		// if (searchLength >= 2) {
			// $('.header').addClass('is-search-autocomplete');
		// }
		// else {
			// $('.header').removeClass('is-search-autocomplete');
		// }
		
	// });
	
	$('.js-search-input').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?route=product/product/autocomplete&filter_name=' +  encodeURIComponent(request),
				dataType: 'json',
				success: function(json) {
					response($.map(json, function(item) {
						return {
							type: 'search',
							product_id: item['product_id'],
							category_id: item['category_id'],
							label: item['name'],
							price: item['price'],
							model: item['model'],
							special: item['special'],
							manufacturer: item['manufacturer'],
							quantity: item['quantity'],
							images: item['images'],	
							stock: item['stock'],	
							isnewest: item['isNewest'],
							sales: item['sales'],
							discount: item['discount'],
							catch: item['catch'],
							nocatch: item['nocatch'],
							popular: item['popular'],
							hit: item['hit'],
							isincart: item['isincart'],
							buy_btn: item['buy_btn'],
							image1: item['image'],
							wish_compare_data: item['wish_compare_data'],
							value: item['href'],
							id: item['product_id'],
							minimum: item['minimum'],
							rating: item['rating'],
							href: item['href_search'],
							show_all: item['show_all'],
							button_to_cart: item['button_to_cart'],
							cart_link: item['cart_link'],
							button_cart: item['button_cart'],
							text_reward: item['text_reward'],
						}
					}));

				}
			});
		},
		'select': function(item) { 
			$('#search_text').val(item['label']);
			window.location.href = item['href']; 
		}
	});


}

$(document).on('mouseenter', '.header__search-menu li', function (event) {
	if (matchMedia('only screen and (min-width: 1199px)').matches ) {
		$(this).addClass('is-open');
	}
}).on('mouseleave', '.header__search-menu li', function(event) {
	if (matchMedia('only screen and (min-width: 1199px)').matches ) {
		$(this).removeClass('is-open');
	}
});


$(document).on('click', function (event) {
	if ( $(event.target).closest('.header__search').length === 0) {
		$('body').removeClass('is-search-autocomplete');
	}
});

// --------------------------------------------------------------------------
// Header Mobile
// --------------------------------------------------------------------------

function headerTriggers($trigger, $triggerClass) {

	$(document).on('click', '.' + $trigger, function(event) {
		if (matchMedia('only screen and (max-width: 1199px)').matches ) {

			event.preventDefault();

			// var $header = $(this).closest('.header');

			if ( $('body').is('.' + $triggerClass) ) {
				$('body').removeClass($triggerClass);
				
			} else {
				$('body').addClass($triggerClass);
			}
			
			if ( ($trigger == 'js-nav-trigger') && $('body').is('.is-nav-open') ) {
				$('.header__mobile ul.header__catalog-menu').html('<img class="loader" src="catalog/view/theme/prostore/images/loader.svg" alt="">');
				$('.header__mobile ul.header__catalog-menu').load('index.php?route=common/header&mobiheader=');
			}
			if ( ($trigger == 'js-catalog-trigger') && $('body').is('.is-catalog-open') ) {
				$('.header__mobile .header__catalog-body ul.header__catalog-menu').html('<img class="loader" src="catalog/view/theme/prostore/images/loader.svg" alt="">');
				$('.header__mobile .header__catalog-body ul.header__catalog-menu').load('index.php?route=common/header&mobiheader=_fix_menu');
			}


		}
		
		if (matchMedia('only screen and (min-width: 1199px)').matches ) {
			if ($trigger == 'js-currency-trigger' || $trigger == 'js-language-trigger' ) {
				event.preventDefault();
			}
		}
		
		if ( $('body').is('.is-nav-open') || $('body').is('.is-call-open') ||$('body').is('.is-catalog-open') || $('body').is('.is-search-open') ) {
			$('body').addClass('is-page-lock');
		} else {
			$('body').removeClass('is-page-lock');
		}
		
		if ( $('body').is('.is-search-open') ) {
			setTimeout(function() {
				$('.header__search-offcanvas .js-search-input').val('').focus();
			}, 100);
		}

		if ( $('body').is('.is-nav-open') ) {
			var currency_language = $('.header__group--currency_language').not(':empty').html();
			$('.header__group--currency_language').empty();
			if ($('.header__nav-group--currency_language').is(':empty')) {					
				$('.header__nav-group--currency_language').html(currency_language);
			}
		} else {
			var currency_language = $('.header__nav-group--currency_language').not(':empty').html();
			$('.header__nav-group--currency_language').empty();
			if ($('.header__group--currency_language').is(':empty')) {
				$('.header__group--currency_language').html(currency_language);
			}
		}

		if ( $('body').is('.is-search-autocomplete') ) {
			$('body').removeClass('is-search-autocomplete').find('form').trigger('reset');
		}

	});
}


$(document).on('click', '.header__catalog-menu li:has(.header__catalog-dropdown) > a', function(event) {

	if (matchMedia('only screen and (max-width: 1199px)').matches ) {

		event.preventDefault();

		if ( $(this).closest('li').is('.is-open') ) {
			$(this).closest('li').removeClass('is-open');

		} else {
			 $(this).closest('li').addClass('is-open');
		}

	}
});

$(document).on('click', '.header__catalog-back', function(event) {
	if (matchMedia('only screen and (max-width: 1199px)').matches ) {
		event.preventDefault();
		$(this).closest('li').removeClass('is-open');
	}
});





// --------------------------------------------------------------------------
// Swiper 5.4.5
// --------------------------------------------------------------------------

function initSwiper() {
	
	$('.js-swiper-intro').each(function( index ) {
		var $swiperIntro = $(this);
		var $effect = $swiperIntro.data('swiper-effect') || 'slide';
		var $speed = $swiperIntro.data('swiper-speed') || 300;
		var $auto = $swiperIntro.data('swiper-auto');
		var $length = $swiperIntro.data('swiper-length');

		
		var swiperIntroInt = new Swiper($swiperIntro[0], {
			slidesPerView: 1,
			spaceBetween: 0,
			effect: $effect,
			speed: $speed,
			autoplay: $auto ? { delay: $auto } : false,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: $length > 1 ? true : false,
			navigation: {
				nextEl: '.js-swiper-intro-next',
				prevEl: '.js-swiper-intro-prev',
			},
			pagination: {
				el: '.js-swiper-intro-pagination',
				type: 'bullets',
				clickable: true,
			},
		});
	});
	


	var ratioSpaceBetween = $(window).width() / 14.7 / 100 * 20;

	$('.js-swiper-products').each(function( index ) {
		var $swiperProducts = $(this);
		var $swiperProductsScrollbar = $swiperProducts.find('.js-swiper-products-scrollbar');

		var swiperProductsInit = new Swiper($swiperProducts[0], {
			slidesPerView: 'auto',
			spaceBetween: 10,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			scrollbar: {
				el:  $swiperProductsScrollbar[0],
				draggable: true,
			},
			breakpoints: {
				1200: {
					slidesPerView: 4,
					spaceBetween: 0,
				}
			}
		});

	});


	$('.js-swiper-products-small').each(function( index ) {
		var $swiperProducts = $(this);
		var $swiperProductsScrollbar = $swiperProducts.find('.js-swiper-products-small-scrollbar');

		var swiperProductsInit = new Swiper($swiperProducts[0], {
			slidesPerView: 'auto',
			spaceBetween: 0,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			scrollbar: {
				el:  $swiperProductsScrollbar[0],
				draggable: true,
			},
			breakpoints: {
				1200: {
					slidesPerView: 4,
					spaceBetween: 0,
				}
			}
		});

	});

	$('.js-swiper-products-order').each(function( index ) {
		var $swiperProducts = $(this);
		var $swiperProductsScrollbar = $swiperProducts.find('.js-swiper-products-order-scrollbar');

		var swiperProductsInit = new Swiper($swiperProducts[0], {
			slidesPerView: 'auto',
			spaceBetween: 0,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			scrollbar: {
				el:  $swiperProductsScrollbar[0],
				draggable: true,
			},
			breakpoints: {
				1200: {
					slidesPerView: 6,
					spaceBetween: 0,
				}
			}
		});

	});

	
	
	// function initSwiperImages() {
		
	// 	$('.js-swiper-images').each(function( index ) {
	// 		var $swiperImages = $(this);
	// 		var $swiperImagesLoop = $swiperImages.find('.swiper-slide:not(.swiper-slide-duplicate)').length > 1 ? true : false;

	// 		var $swiperImagesPrev = $swiperImages.find('.js-swiper-images-prev');
	// 		var $swiperImagesNext = $swiperImages.find('.js-swiper-images-next');
	// 		var $swiperImagesPagination = $swiperImages.find('.js-swiper-images-pagination');

	// 		var swiperImagesInit = new Swiper($swiperImages[0], {
	// 			slidesPerView: 1,
	// 			spaceBetween: 60,
	// 			watchSlidesVisibility: true,
	// 			watchSlidesProgress: true,
	// 			watchOverflow: true,
	// 			loop: $swiperImagesLoop,
	// 			nested: true,
	// 			simulateTouch: false,
	// 			// allowTouchMove: false,
	// 			pagination: {
	// 				el: $swiperImagesPagination[0],
	// 				type: 'bullets',
	// 				clickable: true,
	// 			},
	// 			navigation: {
	// 				nextEl: $swiperImagesNext[0],
	// 				prevEl: $swiperImagesPrev[0],
	// 			},
	// 		});

	// 	});

	// }

	// initSwiperImages();

	var swiperBanners = new Swiper('.js-swiper-banners', {
		slidesPerView: 1,
		spaceBetween: 0,
		watchSlidesVisibility: true,
		watchSlidesProgress: true,
		watchOverflow: true,
		loop: false,
		navigation: {
			nextEl: '.js-swiper-banners-next',
			prevEl: '.js-swiper-banners-prev',
		},
		pagination: {
			el: '.js-swiper-banners-pagination',
			type: 'bullets',
			clickable: true,
		},
	});


	var swiperVideo = new Swiper('.js-swiper-video', {
		slidesPerView: 'auto',
		spaceBetween: 10,
		watchSlidesVisibility: true,
		watchSlidesProgress: true,
		watchOverflow: true,
		loop: false,
		breakpoints: {
			1200: {
				slidesPerView: 4,
				spaceBetween: 0,
			}
		}
	});

	// --------

	// var swiperPartnersOptions = {
		// slidesPerView: 'auto',
		// spaceBetween: 10,
		// watchSlidesVisibility: true,
		// watchSlidesProgress: true,
		// watchOverflow: true,
		// loop: false,
	// };
	// var swiperPartners = new Swiper('.js-swiper-partners', swiperPartnersOptions);


	// ----------

	var swiperGallery = new Swiper('.js-swiper-gallery', {
		slidesPerView: 1,
		spaceBetween: 0,
		watchSlidesVisibility: true,
		watchSlidesProgress: true,
		watchOverflow: true,
		//loop: true,
		navigation: {
			nextEl: '.js-swiper-gallery-next',
			prevEl: '.js-swiper-gallery-prev',
		},
		pagination: {
			el: '.js-swiper-gallery-pagination',
			type: 'bullets',
			clickable: true,
		},
	});

	// ------------


	if (window.matchMedia('(min-width: 1199px)').matches) {

		var swiperIntroRight = new Swiper('.js-swiper-intro-right', {
			slidesPerView: 1,
			spaceBetween: 0,
			watchSlidesVisibility: true,
			watchOverflow: true,
			//loop: true,
			effect: "fade",
			autoplay: {
				disableOnInteraction: true,
			},
			pagination: {
				el: '.js-swiper-intro-pagination-right',
				type: 'bullets',
				clickable: true,
			},
		});
		
		$('.js-swiper-intro-right .swiper-slide').on('mouseover', function() {
			swiperIntroRight.autoplay.stop();
		});

		$('.js-swiper-intro-right .swiper-slide').on('mouseout', function() {
			swiperIntroRight.autoplay.start();
		});
	
		var swiperBenefits = new Swiper('.js-swiper-benefits', {
			slidesPerView: 'auto',
			spaceBetween: 10,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			breakpoints: {
				1200: {
					slidesPerView: 6,
					spaceBetween: 0,
				}
			}
		});

		var swiperBlog = new Swiper('.js-swiper-blog', {
			slidesPerView: 'auto',
			spaceBetween: 10,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			breakpoints: {
				1200: {
					slidesPerView: 4,
					spaceBetween: 0,
				}
			}
		});

		var swiperNews = new Swiper('.js-swiper-news', {
			slidesPerView: 'auto',
			spaceBetween: 10,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			breakpoints: {
				1200: {
					slidesPerView: 4,
					spaceBetween: 0,
				}
			}
		});

		$('.js-swiper-shops').each(function( index ) {
			var $swiperShops = $(this);
			var $swiperShopsScrollbar = $swiperShops.find('.js-swiper-shops-scrollbar');

			var swiperSetInit = new Swiper($swiperShops[0], {
				slidesPerView: 'auto',
				spaceBetween: 10,
				watchSlidesVisibility: true,
				watchSlidesProgress: true,
				watchOverflow: true,
				loop: false,
				grabCursor: true,
				scrollbar: {
					el: $swiperShopsScrollbar[0],
					draggable: true,
				},
				breakpoints: {
					1200: {
						slidesPerView: 4,
						spaceBetween: 0,
					}
				}

			});

		});

		$('.js-swiper-reviews').each(function( index ) {
			var $swiperReviews = $(this);
			var $swiperReviewsScrollbar = $swiperReviews.find('.js-swiper-reviews-scrollbar');

			var swiperSetInit = new Swiper($swiperReviews[0], {
				slidesPerView: 'auto',
				spaceBetween: 10,
				watchSlidesVisibility: true,
				watchSlidesProgress: true,
				watchOverflow: true,
				loop: false,
				grabCursor: true,
				scrollbar: {
					el: $swiperReviewsScrollbar[0],
					draggable: true,
				},
				breakpoints: {
					1200: {
						slidesPerView: 4,
						spaceBetween: 0,
					}
				}

			});

		});

		$('.js-swiper-brands-featured').each(function( index ) {
			var $swiperBrandsFeatured = $(this);
			var $swiperBrandsFeaturedScrollbar = $swiperBrandsFeatured.find('.js-swiper-brands-featured-scrollbar');

			var swiperSetInit = new Swiper($swiperBrandsFeatured[0], {
				slidesPerView: 'auto',
				spaceBetween: 0,
				watchSlidesVisibility: true,
				watchSlidesProgress: true,
				watchOverflow: true,
				loop: false,
				scrollbar: {
					el: $swiperBrandsFeaturedScrollbar[0],
					draggable: true,
				},
				breakpoints: {
					1200: {
						slidesPerView: 6,
						spaceBetween: 0,
					}
				}
			});

		});


		// var swiperCategoriesOptions = {
			// slidesPerView: 'auto',
			// spaceBetween: 10,
			// watchSlidesVisibility: true,
			// watchSlidesProgress: true,
			// watchOverflow: true,
			// loop: false,
		// };
		// var swiperCategories = new Swiper('.js-swiper-categories', swiperCategoriesOptions);
	
	}


	// --------------


	$('.js-swiper-set').each(function( index ) {
		var $swiperSet = $(this);
		var $swiperSetScrollbar = $swiperSet.find('.js-swiper-set-scrollbar');

		var swiperSetInit = new Swiper($swiperSet[0], {
			slidesPerView: 'auto',
			spaceBetween: 10,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			scrollbar: {
				el: $swiperSetScrollbar[0],
				draggable: true,
			},
			breakpoints: {
				1200: {
					slidesPerView: 3,
					spaceBetween: 0,
				}
			}
		});

	});


	// ---------

	// var swiperStoriesThumbs = new Swiper('.js-swiper-stories-thumbs', {
	// 	slidesPerView: 'auto',
	// 	spaceBetween: 0,
	// 	watchSlidesVisibility: true,
	// 	watchSlidesProgress: true,
	// 	watchOverflow: true,
	// 	loop: false,
	// 	grabCursor: true,
	// 	centeredSlides: true,
	// 	slideToClickedSlide: true,
	// });






	// ----------

	var vw = $(window).innerWidth();


	$(window).on('resize orientationchange', function(event) {

		if ( vw != $(window).innerWidth() ) {
			updateSwiper();
			vw = $(window).innerWidth();
		}
		
	});

	function updateSwiper() {

		// if ( $('.js-swiper-partners').length ) {
			// if ( window.matchMedia('(min-width: 1199px)').matches ) {
				// swiperPartners.destroy(true, true);
				// $('.js-swiper-partners').find('.swiper-slide').children().removeAttr('style');
				
			// } else {
				// swiperPartners.destroy(true, true);
				// swiperPartners = new Swiper('.js-swiper-partners', swiperPartnersOptions);
				
			// }
		// }

		// if ( $('.js-swiper-categories').length ) {
			// if ( window.matchMedia('(min-width: 1199px)').matches ) {
				// swiperCategories.destroy(true, true);
				// $('.js-swiper-categories').find('.swiper-slide').children().removeAttr('style');
				
			// } else {
				// swiperCategories.destroy(true, true);
				// swiperCategories = new Swiper('.js-swiper-categories', swiperCategoriesOptions);
				
			// }
		// }
		
		if ( $('.js-swiper-products-categories').length ) {
			if ( window.matchMedia('(min-width: 1199px)').matches ) {
				swiperProductsCategories.destroy(true, true);
				$('.js-swiper-products-categories').find('.swiper-slide').children().removeAttr('style');
				
			} else {
				swiperProductsCategories.destroy(true, true);
				swiperProductsCategories = new Swiper('.js-swiper-products-categories', swiperProductsCategoriesOptions);
				
			}
		}

	}
		
	updateSwiper();
}


// ---------


function initSwiperSku() {
	

	$('.js-swiper-vertical').each(function( index ) {

		var $vertical = $(this);
		
		var $verticalSlides =  $vertical.find('.js-swiper-vertical-slides');
		var $verticalThumbs =  $vertical.find('.js-swiper-vertical-thumbs');

		var $verticalPrev = $vertical.find('.js-swiper-vertical-prev');
		var $verticalNext = $vertical.find('.js-swiper-vertical-next');
		

		var swiperThumbs = new Swiper($verticalThumbs, {
			direction: 'horizontal',
			slidesPerView: 'auto',
			spaceBetween: 0,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			simulateTouch: false,
			navigation: {
				nextEl: $verticalNext[0],
				prevEl: $verticalPrev[0],
			},
			breakpoints: {
				768: {
					direction: 'vertical',
					slidesPerView: 5,
				},
			}
			
		});

		var swiperSlides = new Swiper($verticalSlides, {
			slidesPerView: 1,
			spaceBetween: 60,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			grabCursor: true,
			// simulateTouch: false,
			navigation: {
				nextEl: $verticalNext[0],
				prevEl: $verticalPrev[0],
			},
			thumbs: {
				swiper: swiperThumbs
			},
			on: {
				slideChange: function() {
					swiperThumbs.slideTo(this.realIndex);
				}
				
			}
	
		});


		$(document).on('click', '.js-swiper-vertical-thumbs .swiper-slide', function() {
			var index = $(this).index();
			swiperSlides.slideTo(index);
		});


		$(window).on('resize', function(){
			swiperThumbs.update();
			swiperSlides.update();
		});

	});




	// ---------

	$('.js-swiper-horizontal').each(function( index ) {

		var $horizontal = $(this);
		
		var $horizontalSlides =  $horizontal.find('.js-swiper-horizontal-slides');
		var $horizontalThumbs =  $horizontal.find('.js-swiper-horizontal-thumbs');

		var $horizontalPrev = $horizontal.find('.js-swiper-horizontal-prev');
		var $horizontalNext = $horizontal.find('.js-swiper-horizontal-next');
		

		var swiperThumbs = new Swiper($horizontalThumbs, {
			direction: 'horizontal',
			slidesPerView: 'auto',
			spaceBetween: 0,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			simulateTouch: false,
			navigation: {
				nextEl: $horizontalNext[0],
				prevEl: $horizontalPrev[0],
			},
			breakpoints: {
				768: {
					direction: 'horizontal',
					slidesPerView: 5,
				},
			}
			
		});

		var swiperSlides = new Swiper($horizontalSlides, {
			slidesPerView: 1,
			spaceBetween: 60,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			grabCursor: true,
			// simulateTouch: false,
			navigation: {
				nextEl: $horizontalNext[0],
				prevEl: $horizontalPrev[0],
			},
			thumbs: {
				swiper: swiperThumbs
			},
			on: {
				slideChange: function() {
					swiperThumbs.slideTo(this.realIndex);
				}
				
			},
	
		});


		$(document).on('click', '.js-swiper-horizontal-thumbs .swiper-slide', function() {
			var index = $(this).index();
			swiperSlides.slideTo(index);
		});

	});


}


// --------------------------------------------------------------------------
// Products Gallery Mouseover
// --------------------------------------------------------------------------

function GalleryMouseover() {

	$('.products__item-gallery').each(function( index ) {
		 var $mouseoverGallery = $(this);
		// var $mouseoverGalleryLength = $mouseoverGallery.find('.products__item-image').length;

		// $mouseoverGallery.append('<div class="products__item-pagination"></div>')

		// for ( var i = 0 ; i < $mouseoverGalleryLength ; i++ ) {
			// $mouseoverGallery.find('.products__item-pagination').append('<div class="products__item-bullet"></div>');
		// }

		// $mouseoverGallery.find('.products__item-image').eq(0).addClass('is-active');
		// $mouseoverGallery.find('.products__item-bullet').eq(0).addClass('is-active');

		$(document).on('mouseenter', '.products__item-bullet', function(){
			var index = $(this).index();
			var move  = index * 100 * -1;
			var img = $(this).closest('.products__item-gallery').find('.products__item-image').eq(index);
			img.find('[data-src]').each(function(){
				$(this).attr('src', $(this).attr('data-src')).removeAttr('data-src');
			});
			img.addClass('is-active').css('transform', 'translate('+move+'%,0)').siblings().removeClass('is-active');
			$(this).closest('.products__item-gallery').find('.products__item-bullet').eq(index).addClass('is-active').siblings().removeClass('is-active');

		});
		
		$mouseoverGallery.on('mouseleave', function(){
			$mouseoverGallery.find('.products__item-image').eq(0).addClass('is-active').siblings().removeClass('is-active');
			$mouseoverGallery.find('.products__item-bullet').eq(0).addClass('is-active').siblings().removeClass('is-active');
		});
	
	});
}


// --------------------------------------------------------------------------
// Sticky
// --------------------------------------------------------------------------





function stickySku() {

	if ( $('.sku__sticky').length ) {


		if (window.matchMedia('(min-width: 1199px)').matches && $('.sku__sticky').innerHeight() < $('.sku__desc').innerHeight()) {

			var $triggerTop = 0;

			if ( $('body').is('.is-page-header-fixed') ) {
				$triggerTop = $('.header-fixed').data('fixed-height') || $('.header-fixed').innerHeight();
			}

			$('.sku__sticky').stick_in_parent({
				offset_top: $triggerTop,
				bottoming: true,
				spacer: false,
				inner_scrolling: true,
				sticky_class: 'is-sticky',
				parent: '.sku__view'
			});


		} else {

			$('.sku__sticky').trigger('sticky_kit:detach');

		}

	}

}



function stickyPersonal() {

	if ( $('.categories-aside--nav').length ) {


		if (window.matchMedia('(min-width: 1199px)').matches) {


			$('.categories-aside--nav').stick_in_parent({
				offset_top: 20,
				bottoming: true,
				spacer: false,
				inner_scrolling: true,
				sticky_class: 'is-sticky',
				parent: '.personal__wrapper'
			});


		} else {

			$('.categories-aside--nav').trigger('sticky_kit:detach');

		}

	}
}


$(window).on('load resize orientationchange', function(){
	stickySku();
	stickyPersonal();
});

// --------------------------------------------------------------------------
// Another Triggers
// --------------------------------------------------------------------------

function toggle() {

	$(document).on('click', '.js-toggle-btn', function(event) {
		if (matchMedia('only screen and (max-width: 1199px)').matches ) {
			event.preventDefault();
			if ( $(this).closest('.js-toggle').is('.is-open') ) {
				$(this).closest('.js-toggle').removeClass('is-open');
				
			} else {
				$(this).closest('.js-toggle').addClass('is-open');
			}
		}
	});

	$(document).on('click', function (event) {
		if ( $(event.target).closest('.js-toggle').length === 0) {
			$('.js-toggle').removeClass('is-open');
		}
	});



	$(document).on('mouseenter', '.js-toggle', function (event) {
		if (matchMedia('only screen and (min-width: 1199px)').matches ) {
			$(this).addClass('is-open');
		}
	}).on('mouseleave', '.js-toggle', function(event) {
		if (matchMedia('only screen and (min-width: 1199px)').matches ) {
			$(this).removeClass('is-open');
		}
	});

}



// --------------------------------------------------------------------------
// Data
// --------------------------------------------------------------------------


$(document).on('click', '.js-data-toggle', function(event){
	if (matchMedia('only screen and (max-width: 1199px)').matches ) {
		event.preventDefault();

		if ( $(this).closest('.js-data').is('.is-open') ) {
			$(this).closest('.js-data').removeClass('is-open').find('.js-data-content').slideUp('fast');
			
		} else {
			$(this).closest('.js-data').addClass('is-open').find('.js-data-content').slideDown('fast');
		}

	}
});

$(window).on('load resize', function(){
	if (matchMedia('only screen and (min-width: 1199px)').matches ) {
		$('.js-data').removeClass('is-open').find('.js-data-content').removeAttr('style');
	}
});


// --------------------------------------------------------------------------
// Cart
// --------------------------------------------------------------------------

$(document).on('click', '.js-action-toggle', function(event){
	event.preventDefault();
	if ( $(this).closest('.js-action').is('.is-open') ) {
		$(this).closest('.js-action').removeClass('is-open');
		
	} else {
		$(this).closest('.js-action').addClass('is-open');
	}
});

$(document).on('click', function (event) {
	if ( $(event.target).closest('.js-action').length === 0) {
		$('.js-action').removeClass('is-open');
	}
});

// --------------------------------------------------------------------------
// Chat
// --------------------------------------------------------------------------


$(document).on('click', '.js-chat-toggle', function(event){
	event.preventDefault();

	if ( $('html').is('.is-chat-open') ) {
		$('html').removeClass('is-chat-open');
		
	} else {
		$('html').addClass('is-chat-open');
	}
});

$(document).on('click', function(event){
	if ( $(event.target).closest('.js-chat').length === 0) {
		$('html').removeClass('is-chat-open');
	}
});


// --------------------------------------------------------------------------
// Collapse
// --------------------------------------------------------------------------


$(document).on('click', '.js-collapse-toggle', function(event){

	
	event.preventDefault();

	if ( $(this).closest('.js-collapse').is('.is-open') ) {
		$(this).closest('.js-collapse').removeClass('is-open').find('.js-collapse-content').slideUp('fast');
		
	} else {
		$(this).closest('.js-collapse').addClass('is-open').find('.js-collapse-content').slideDown('fast');
	}
});


// --------------------------------------------------------------------------
// Categories Toggle
// --------------------------------------------------------------------------


$(document).on('click', '.categories-aside__menu > li:has(.categories-aside__dropdown) > .categories-aside__link > span', function(event){

	if (matchMedia('only screen and (min-width: 1199px)').matches ) {
		event.preventDefault();

		if ( $(this).closest('li').is('.is-open') ) {
			$(this).closest('li').removeClass('is-open').find('.categories-aside__dropdown').slideUp('fast', function() {
				if ( $('.categories-aside--nav').length ) {
					if (window.matchMedia('(min-width: 1199px)').matches) {
						$('.categories-aside--nav').trigger('sticky_kit:recalc');
					}
				}
			});
		} else {
			$(this).closest('li').addClass('is-open').find('.categories-aside__dropdown').slideDown('fast', function() {
				if ( $('.categories-aside--nav').length ) {
					if (window.matchMedia('(min-width: 1199px)').matches) {
						$('.categories-aside--nav').trigger('sticky_kit:recalc');
					}
				}
			});
		}
		
	}

});

$(document).on('mouseenter', '.categories-aside__submenu > li:has(.categories-aside__dropright)', function(event){

	if (matchMedia('only screen and (min-width: 1199px)').matches ) {
		$(this).addClass('is-open')
	}

}).on('mouseleave', '.categories-aside__submenu > li:has(.categories-aside__dropright)', function(event){

	if (matchMedia('only screen and (min-width: 1199px)').matches ) {
		$(this).removeClass('is-open')
	}

});


$(document).on('click', '.categories-aside__menu > li:has(.categories-aside__dropdown) > .categories-aside__link, .categories-aside__submenu > li:has(.categories-aside__dropright) > .categories-aside__sublink', function(event){
	if (matchMedia('only screen and (max-width: 1199px)').matches ) {
		event.preventDefault();

		if ( $(this).closest('li').is('.is-open') ) {
			$(this).closest('li').removeClass('is-open');
			
		} else {
			$(this).closest('li').addClass('is-open');
		}
	}

});

$(document).on('click', '.categories-aside__back', function(event){
	if (matchMedia('only screen and (max-width: 1199px)').matches ) {
		event.preventDefault();
		$(this).closest('li').removeClass('is-open');
	}

});


// --------------------------------------------------------------------------
// Change products quantity In the Cart
// --------------------------------------------------------------------------

$(document).on('change keydown','.header__cart-item-number .ui-number__input,.cart__item-number .ui-number__input,.products__item-action .ui-number__input,#product .ui-number__input', function(e){
	var qty = 0;
	var name;
	qty = $(this).val();
	if((e.type == 'keydown' && e.keyCode != 13) || qty < 0 ){ return; }
	name = $(this).attr('name');
	cart.update(name,qty);
});

	
// --------------------------------------------------------------------------
// Filter
// --------------------------------------------------------------------------

$(document).on('click', '.filter-aside__toggle, .filter-aside__close', function(event){
	event.preventDefault();
	if ( $('html').is('.is-filter-open') ) {
		$('html').removeClass('is-filter-open is-page-lock');
		
	} else {
		$('html').addClass('is-filter-open is-page-lock');
	}
});

// --------------------------------------------------------------------------
// Show more button for module prostore_product_tabs
// --------------------------------------------------------------------------

$(document).on('click', '.show-more', function (event) {
	event.preventDefault();
	var module_id = $(this).data('for');
	var container = $('#prodtab_'+module_id);
	var href = 'index.php?route=extension/module/prostore_product_tabs/showmore #prodtab_'+module_id+' div:first';
	if (module_id) {
		container.load(href,{type: 'post',module_id: module_id},function(){
			activateElements();
			}); 
	}
});

// --------------------------------------------------------------------------
// Categories
// --------------------------------------------------------------------------

function Categories() {
	$(document).on('click', '.js-categories-toggle, .categories-aside__close', function(event){
		event.preventDefault();
		if ( $('html').is('.is-categories-open') ) {
			$('html').removeClass('is-categories-open is-page-lock');
			
		} else {
			$('html').addClass('is-categories-open is-page-lock');
		}
	});

	if ( $('.categories-aside').length ) {
		var title = $('.categories-aside__title').text();
		$('.settings > .row').prepend('<div class="col-auto is-xl-hidden"><button class="ui-btn ui-btn--46 ui-btn--white js-categories-toggle">' + title +'<svg class="icon-catalog"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-catalog"></use></svg></button></div>');

	}

	// --------------------------------------------------------------------------
	// Show more button for Categories
	// --------------------------------------------------------------------------
	
	$(document).on('click', '.show-more-prostore', function (event) {
		event.preventDefault();
		if ($(this).is("[disabled]")) {
			return;
		}
		
		$(this).attr("disabled", true);
		$(this).text($(this).data('loading-text'));

		var container = '#mainContainer .products ul.products__list';
		var pagination = '.container-pagination';
		var href = $(this).attr('href');

		$.get(href,function(data){
			var html = $(data).find(container).html();				
			$(container).append(html);
			html = $(data).find(pagination).html();
			$(pagination).html(html);
			if (html) {
				window.history.pushState({}, '', href);
				activateElements();
			}
		}); 
	});		
}

// --------------------------------------------------------------------------
// Tabs
// --------------------------------------------------------------------------

$(document).on('click', '[data-tabs-btn]', function(event) {

	event.preventDefault();

	var tab = $(this),
		tab_id = $(this).attr('data-tabs-btn'),
		sku = $('.sku'),
		tabs = $('.details__tabs');
		
	if ($(this).is('[data-scroll-top]')) {
		var tab_target = $('[data-tabs-content=' + tab_id + ']').offset().top - 10;

		if ( $('.js-sku-compact').length ) {
			tab_target = tab_target - $('.js-sku-compact').innerHeight();
		}
		if (tabs.length) {
			tab_target = tab_target - 15;
		}
	}

	if ($(this).is('.is-active')) {
		if (sku.length) {
			$('[data-tabs-btn], [data-tabs-content]').removeClass('is-active');
		} else {
			$(this).closest('[data-tabs]').find('[data-tabs-btn]').removeClass('is-active');
			$(this).closest('[data-tabs]').find('[data-tabs-content]').removeClass('is-active');
		}
	} else {
		if (sku.length) {
			$('[data-tabs-btn], [data-tabs-content]').removeClass('is-active');
		} else {
			$(this).closest('[data-tabs]').find('[data-tabs-btn]').removeClass('is-active');
			$(this).closest('[data-tabs]').find('[data-tabs-content]').removeClass('is-active');
		}
		$('[data-tabs-btn=' + tab_id + '], [data-tabs-content=' + tab_id + ']').addClass('is-active');

		if ($(this).is('[data-scroll-top]')) {
			$('html, body').animate({
				scrollTop: tab_target
			}, 'fast');
		}
	}

});


// --------------------------------------------------------------------------
// Readmore
// --------------------------------------------------------------------------

function initReadmore() {
	if ( $('.js-readmore').length ) {
	

		$('.js-readmore').each(function( index ) {
			var $readmore = $(this);
			var $redmoreMoreLink = $readmore.data('readmore-toggle') || 'Подробнее';
			var $redmoreLessLink = $readmore.data('readless-toggle') || 'Свернуть';
			var $redmoreCollapsedHeight = $readmore.data('collapsedheight-toggle') || 150;

			$readmore.readmore({
				speed: 400,
				collapsedHeight: $redmoreCollapsedHeight,
				heightMargin: 18,
				moreLink: '<a href="#" class="ui-link ui-link--blue">'+ $redmoreMoreLink +' <svg class="icon-arrow-link"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-arrow-link"></use></svg></a>',
				lessLink: '<a href="#" class="ui-link ui-link--blue">'+ $redmoreLessLink +' <svg class="icon-arrow-link"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-arrow-link"></use></svg></a>',
				embedCSS: true,
				blockCSS: 'display: block; width: 100%;',
				startOpen: false,
				beforeToggle: function() {},
				afterToggle: function() {},
				blockProcessed: function() {}
			});

		});

	}
		

}


// --------------------------------------------------------------------------
// Accordion Tabs
// --------------------------------------------------------------------------


$(document).on('click', '[data-accordion-btn]', function(event) {

	event.preventDefault();

	var tab = $(this),
		tab_id = $(this).attr('data-accordion-btn');
		tab_target = $('[data-accordion-content=' + tab_id + ']').offset().top - 10;

	
		if (window.matchMedia('(min-width: 1199px)').matches) {
			if ( $('.js-sku-compact').length ) {
				tab_target = tab_target - $('.js-sku-compact').innerHeight();
			}

			if ( $('body').is('.is-page-header-fixed') ) {

				tab_target -= $('.header-fixed').data('fixed-height') || $('.header-fixed').innerHeight();
			}
		} else {
			tab_target = tab_target - $('.header__mobile-fixed').innerHeight();
		}

		if ( $(this).is('.is-active') ) {
			$('[data-accordion]').find('[data-accordion-btn=' + tab_id + '], [data-accordion-content=' + tab_id + ']').removeClass('is-active').find('[data-accordion-collapse]').slideUp('fast');
		} else {

			// $('[data-accordion]').find('[data-accordion-btn], [data-accordion-content]').removeClass('is-active')
			// $('[data-accordion]').find('[data-accordion-collapse]').slideUp('fast');


			$('html, body').animate({
				scrollTop: tab_target
			}, 'fast');


			$('[data-accordion]').find('[data-accordion-btn=' + tab_id + '], [data-accordion-content=' + tab_id + ']').addClass('is-active').find('[data-accordion-collapse]').slideDown('fast');
			
			// Update Readmore
			initReadmore();
		
		}

		$(window).trigger('resize');

});





// --------------------------------------------------------------------------
// Sku Sticky
// --------------------------------------------------------------------------

function compactSku() {
	
	if ( $('.js-sku-compact').length ) {

		var scrolling = $(window).scrollTop();
		
		if (window.matchMedia('(min-width: 1199px)').matches) {
			if (scrolling > $('.js-sku-view').offset().top + $('.js-sku-view').innerHeight()  ) {
				$('html').addClass('is-sku-compact');
			} else {
				$('html').removeClass('is-sku-compact');
			}
		} else {
			$('html').addClass('is-sku-compact');
		}
	}

}


$(window).on('scroll', function(event) {
	compactSku();
});

// --------------------------------------------------------------------------
// Select
// --------------------------------------------------------------------------
/* 
function initSelectric() {

	if ( $('.ui-select').length ) {


		$('.ui-select > select').selectric({
			maxHeight: 'auto',
			keySearchTimeout: 500,
			arrowButtonMarkup: '<span class="arrow"><svg width="11" height="7" viewBox="0 0 11 7" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.284143 1.91713L4.77096 6.66853C4.86819 6.77356 4.98386 6.85693 5.11131 6.91382C5.23876 6.97071 5.37547 7 5.51353 7C5.6516 7 5.7883 6.97071 5.91575 6.91382C6.0432 6.85693 6.15888 6.77356 6.25611 6.66853L10.6906 1.91713C10.7887 1.81296 10.8665 1.68902 10.9196 1.55246C10.9727 1.4159 11 1.26943 11 1.1215C11 0.973565 10.9727 0.827095 10.9196 0.690537C10.8665 0.553981 10.7887 0.43004 10.6906 0.325864C10.4947 0.11715 10.2296 -8.86323e-07 9.95329 -8.62168e-07C9.67698 -8.38012e-07 9.4119 0.11715 9.21594 0.325864L5.51353 4.29283L1.81113 0.325865C1.61632 0.118839 1.35339 0.00212611 1.07901 0.000888261C0.941366 3.56885e-05 0.804918 0.0283026 0.677491 0.0840692C0.550063 0.139836 0.434163 0.222006 0.336436 0.325865C0.234908 0.426292 0.152959 0.54728 0.095324 0.681838C0.0376887 0.816396 0.00551076 0.961857 0.000647984 1.10982C-0.00421384 1.25778 0.0183358 1.4053 0.0669951 1.54387C0.115654 1.68244 0.189458 1.8093 0.284143 1.91713Z" fill="currentColor"/></svg></span>',
			disableOnMobile: false,
			nativeOnMobile: false,
			openOnHover: false,
			hoverIntentTimeout: 500,
			expandToItemText: false,
			responsive: true,
			customClass: {
				prefix: 'selectric',
				camelCase: false
			},
			optionsItemBuilder: '{text}',
			labelBuilder: '{text}',
			preventWindowScroll: false,
			inheritOriginalWidth: false,
			allowWrap: true,
			multiple: {
				separator: ', ',
				keepMenuOpen: true,
				maxLabelEntries: false
			},
			optionsItemBuilder: function(itemData, element, index) {
				return itemData.text;
			},
			labelBuilder: function(itemData, index) {

				return itemData.text;

			},
			onBeforeInit: function() {

			},
			onInit: function(itemData, element, index) {

			},
			onBeforeOpen: function(element) {

			},
			onOpen: function(element) {
				
			},
			onBeforeClose: function() {
				
			},
			onClose: function(element) {
				
			},
			onBeforeChange: function() {

			},
			onChange: function(element) {
				$(element).trigger('change').closest('.selectric-wrapper').addClass('selectric-changed');
			},
			onRefresh: function() {

			},
		});

	}

}

*/

// --------------------------------------------------------------------------
// Number
// --------------------------------------------------------------------------


$(document).on('keyup', '.ui-number__input', function(event) {

	var input_val = $(this).val(),
		input_max = $(this).is('[max]') ? $(this).attr('max') : 9999;
	
	if ( input_val > input_max.length ) {
		var val = input_val.slice(0, input_max.length);
		$(this).val(val);
	}

});

$(document).on('click', '.ui-number__increase', function(event) {
	event.preventDefault();
	
	var input = $(this).closest('.ui-number').find('.ui-number__input'),
		input_max = input.is('[max]') ? input.attr('max') : 9999,
		input_step = input.is('[step]') ? parseInt( input.attr('step')) : 1 ,
		input_val = parseInt( input.val() );

	if ( input_max > input_val ) {
		input.val(input_val + input_step);
		input.change();
	}

});

$(document).on('click', '.ui-number__decrease', function(event) {
	event.preventDefault();
	
	var input = $(this).closest('.ui-number').find('.ui-number__input'),
		input_min = input.is('[min]') ? input.attr('min') : 1,
		input_step = input.is('[step]') ? parseInt( input.attr('step')) : 1 ,
		input_val = parseInt( input.val() );


	if ( input_min < input_val ) {
		input.val(input_val - input_step);
		input.change();
	}

});


// --------------------------------------------------------------------------
// Change Category View
// --------------------------------------------------------------------------

$(document).on('click', '.js-options-item', function (event) {
	event.preventDefault();
	
	$(this).addClass('is-active').siblings().removeClass('is-active');
	
	var sub = $(this);
	if ($(this).attr('data-href') != undefined) {
		location = $(this).attr('data-href');
	}else if ($(this).attr('data-option') != undefined) {
		var view = sub.attr("data-option");
		$('#mainContainer').load(window.location.href,{type: 'post',view: view},function(){
			activateElements();
		}); 
	}
});

function activateElements() {
	//fancyFastCart();
   // activateDatepicker();
   // activateUploadBtn();
   // formstyler();
  //  fancyPopUp();
	GalleryMouseover();
	countdown();
	initReadmore();
}

// --------------------------------------------------------------------------
// Change Category View OC default
// --------------------------------------------------------------------------

function categoryViewOC() {
	// Product List
	$('#list-view').click(function() {
		$('#content .product-grid > .clearfix').remove();

		$('#content .row > .product-grid').attr('class', 'product-layout product-list col-xs-12');
		$('#grid-view').removeClass('active');
		$('#list-view').addClass('active');

		localStorage.setItem('display', 'list');
	});

	// Product Grid
	$('#grid-view').click(function() {
		// What a shame bootstrap does not take into account dynamically loaded columns
		var cols = $('#column-right, #column-left').length;

		if (cols == 2) {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-6 col-md-6 col-sm-12 col-xs-12');
		} else if (cols == 1) {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-4 col-md-4 col-sm-6 col-xs-12');
		} else {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-3 col-md-3 col-sm-6 col-xs-12');
		}

		$('#list-view').removeClass('active');
		$('#grid-view').addClass('active');

		localStorage.setItem('display', 'grid');
	});


	if (localStorage.getItem('display') == 'list') {
		$('#list-view').trigger('click');
		$('#list-view').addClass('active');
	} else {
		$('#grid-view').trigger('click');
		$('#grid-view').addClass('active');
	}
}

// --------------------------------------------------------------------------
//  Bootstrap tooltip
// --------------------------------------------------------------------------

function bootstrapTooltip() {
	
	if ($('body').is('.bootstrap-5')) {
		if ($('[data-toggle="tooltip"]').length ) {
			var tooltip = new bootstrap.Tooltip($('[data-toggle="tooltip"]'))
		}
	} else {
		$('[data-toggle="tooltip"]').tooltip();
	}
}

	
// --------------------------------------------------------------------------
//  Add To Cart
// --------------------------------------------------------------------------

function initCartButtons(json) {
	// --------------------------------------------------------------------------
	//  init Cart Buttons afrer remove products from cart
	// --------------------------------------------------------------------------	
	if ( json['deleted_items'] != undefined && json['deleted_items'].length) {
		for (let index = 0; index < json['deleted_items'].length; index++) {
			toCartButtonCommonReset(json['deleted_items'][index]);
			toCartButtonReset(json['deleted_items'][index]);
		}
	}	
}

function toCartButtonCommon(product_id) {
	// --------------------------------------------------------------------------
	//  Change cart button in other pages
	// --------------------------------------------------------------------------	
	var buttonToCange = $('.ui-add-to-cart a[data-for="'+product_id+'"]').siblings('button.ui-btn--primary');
	if (buttonToCange.length) {
		let button = buttonToCange.closest('.ui-add-to-cart').addClass('is-active').find('.ui-btn--primary');
		let button_text = (button.data('add-to-cart') || 'Перейти <br> в корзину');
		button_text += ' <svg class="icon-cart"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-cart"></use></svg>';
		button.html(button_text);
		buttonToCange.unbind('click');
		buttonToCange.attr('onclick',"window.location.href='index.php?route=checkout/cart';");
	}	

};

function toCartButtonCommonReset(product_id) {
	// --------------------------------------------------------------------------
	//  Change cart button in other pages
	// --------------------------------------------------------------------------	
	var buttonToCange = $('.ui-add-to-cart a[data-for="'+product_id+'"]').siblings('button.ui-btn--primary');
	if (buttonToCange.length) {
		//qty = $('input[name="prod_id_quantity['+product_id+']"]').prop("defaultValue");
		qty = $('input[name="prod_id_quantity['+product_id+']"]').is('[min]') ? $('input[name="prod_id_quantity['+product_id+']"]').attr('min') : 1;
		$('input[name="prod_id_quantity['+product_id+']"]').val(qty);
		buttonToCange.closest('.ui-add-to-cart').removeClass('is-active').find('.ui-btn--primary').html((buttonToCange.data('button-cart') || 'Купить') + ' <svg class="icon-cart"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-cart"></use></svg>');
		buttonToCange.unbind('click');
		buttonToCange.attr('onclick',"cart.add('"+product_id+"', '"+qty+"');");
	}	

};


function toCartButton() {
	// --------------------------------------------------------------------------
	//  Change cart button in product page only. Replaces standart button with GOTO CART button
	// --------------------------------------------------------------------------	
	var buttonToCange = $('#button-cart,#button-cart-additional');
	if (!buttonToCange.length) {
		return;
	}
	buttonToCange.each(function(index, el) {
		buttonCart = $(el);
		buttonCart.closest('.ui-add-to-cart').addClass('is-active').find('.ui-btn--primary span').text((buttonCart.data('add-to-cart') || 'Перейти в корзину'));
		buttonCart.unbind('click');
		buttonCart.attr('onclick',"window.location.href='index.php?route=checkout/cart';");
		buttonCart.removeAttr("id");
	});

};

function toCartButtonReset(product_id) {
	// --------------------------------------------------------------------------
	//  Change cart button and sticky cart button in product page only. Replace GOTO CART button with standart button
	// --------------------------------------------------------------------------
	let sticky_button = '.sku__compact-wrapper .ui-add-to-cart button.ui-btn--primary';
	let cart_button = $('#product .sku__action .ui-add-to-cart button.ui-btn--primary,'+sticky_button);
	let product_marker = $('#product .sku__action input[name="prod_id_quantity['+product_id+']"]').length;
	if (cart_button.closest('.ui-add-to-cart').hasClass('is-active') && product_marker) {
		qty = $('input[name="prod_id_quantity['+product_id+']"]').is('[min]') ? $('input[name="prod_id_quantity['+product_id+']"]').attr('min') : 1;
		$('input[name="prod_id_quantity['+product_id+']"]').val(qty);		
		cart_button.closest('.ui-add-to-cart').removeClass('is-active').find('.ui-btn--primary span').text(cart_button.data('button-cart') || 'Купить');
		cart_button.removeAttr('onclick');
		cart_button.attr('id','button-cart');
		// For sticky button set different id
		$(sticky_button).attr('id','button-cart-additional');		
	}		
};


// --------------------------------------------------------------------------
//  Add To Wish&Compare Product page
// --------------------------------------------------------------------------


$(document).on('click', '.sku__addto-btn', function(event) { 
	var action = $(this).data('action');
	var product_id = $(this).data('for');
	var mode = 'add';

	if ($(this).hasClass('is-active')) {
		mode = 'remove';
	}

	var selectScript = new Function('product_id', action+"."+mode+"(product_id)");
	selectScript(product_id); 
});

// --------------------------------------------------------------------------
//  Add To Wish&Compare Category and so one
// --------------------------------------------------------------------------

$(document).on('click', '.ui-btn--compare,.ui-btn--favorite', function(event) { 
	var action = $(this).data('action');
	var product_id = $(this).data('for');
	var mode = 'add';
	if ($(this).hasClass('is-active')) { 
		mode = 'remove';
	}
	var selectScript = new Function('product_id', action+"."+mode+"(product_id)");
	selectScript(product_id);
});


// --------------------------------------------------------------------------
//  Alert
// --------------------------------------------------------------------------

$(document).on('click', '.alert__close', function(event) {
	event.preventDefault();
	alertClose(this);
});

function alertClose(alert) {
	$(alert).closest('.alert').addClass('is-hide').on('animationend',function(){
		$(alert).closest('.alert').remove();
	});	
}

function alertAutoClose() {
	let alerts = $('.alerts-wrapper');
	$(alerts).addClass('is-auto-close');
	setTimeout(function () {
		$(alerts).removeClass('is-auto-close');
		$(alerts).find('.alert--green').each(
			function(){
				alertClose(this);
			}			
		);
	}, 5000);
};


// --------------------------------------------------------------------------
// Fancybox
// --------------------------------------------------------------------------

var fancyboxOptions = {
	infobar : false,
	toolbar : false,
	clickOutside: true,
	touch: true,
	transitionEffect : 'slide',
	lang: 'ru',
	smallBtn: true,
	closeExisting: false,
	hideScrollbar: true,
	preventCaptionOverlap: true,
	autoFocus: false,
	backFocus: false,
	trapFocus: false,
	loop: true,

	thumbs: {
		autoStart: true,
		hideOnClose: true,
		parentEl: ".fancybox-container",
		axis: 'x',
	},
	buttons: [
		"close"
	],
	i18n: {
		ru: {
			CLOSE: "Закрыть",
			NEXT: "Вперед",
			PREV: "Назад",
			ERROR: "Запрашиваемый контент не может быть загружен. <br/> Пожалуйста, попробуйте позже.",
		}
	},
	btnTpl: {
		close: 
			'<button data-fancybox-close class="fancybox-close" title="{{CLOSE}}">' +
				'<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.7612 9.99893L19.6305 2.14129C19.8657 1.90606 19.9979 1.58701 19.9979 1.25434C19.9979 0.921668 19.8657 0.602622 19.6305 0.367388C19.3953 0.132153 19.0763 0 18.7437 0C18.411 0 18.092 0.132153 17.8568 0.367388L10 8.23752L2.14319 0.367388C1.90799 0.132153 1.58897 2.95361e-07 1.25634 2.97839e-07C0.923701 3.00318e-07 0.604689 0.132153 0.36948 0.367388C0.134271 0.602622 0.00213201 0.921668 0.002132 1.25434C0.002132 1.58701 0.134271 1.90606 0.36948 2.14129L8.23878 9.99893L0.36948 17.8566C0.252404 17.9727 0.159479 18.1109 0.0960643 18.2631C0.0326494 18.4153 0 18.5786 0 18.7435C0 18.9084 0.0326494 19.0717 0.0960643 19.224C0.159479 19.3762 0.252404 19.5143 0.36948 19.6305C0.4856 19.7476 0.623751 19.8405 0.775965 19.9039C0.928178 19.9673 1.09144 20 1.25634 20C1.42123 20 1.5845 19.9673 1.73671 19.9039C1.88892 19.8405 2.02708 19.7476 2.14319 19.6305L10 11.7603L17.8568 19.6305C17.9729 19.7476 18.1111 19.8405 18.2633 19.9039C18.4155 19.9673 18.5788 20 18.7437 20C18.9086 20 19.0718 19.9673 19.224 19.9039C19.3762 19.8405 19.5144 19.7476 19.6305 19.6305C19.7476 19.5143 19.8405 19.3762 19.9039 19.224C19.9674 19.0717 20 18.9084 20 18.7435C20 18.5786 19.9674 18.4153 19.9039 18.2631C19.8405 18.1109 19.7476 17.9727 19.6305 17.8566L11.7612 9.99893Z" fill="currentColor"/></svg>' +
			'</button>',
		smallBtn:
			'<button data-fancybox-close class="fancybox-close" title="{{CLOSE}}">' +
			'<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.7612 9.99893L19.6305 2.14129C19.8657 1.90606 19.9979 1.58701 19.9979 1.25434C19.9979 0.921668 19.8657 0.602622 19.6305 0.367388C19.3953 0.132153 19.0763 0 18.7437 0C18.411 0 18.092 0.132153 17.8568 0.367388L10 8.23752L2.14319 0.367388C1.90799 0.132153 1.58897 2.95361e-07 1.25634 2.97839e-07C0.923701 3.00318e-07 0.604689 0.132153 0.36948 0.367388C0.134271 0.602622 0.00213201 0.921668 0.002132 1.25434C0.002132 1.58701 0.134271 1.90606 0.36948 2.14129L8.23878 9.99893L0.36948 17.8566C0.252404 17.9727 0.159479 18.1109 0.0960643 18.2631C0.0326494 18.4153 0 18.5786 0 18.7435C0 18.9084 0.0326494 19.0717 0.0960643 19.224C0.159479 19.3762 0.252404 19.5143 0.36948 19.6305C0.4856 19.7476 0.623751 19.8405 0.775965 19.9039C0.928178 19.9673 1.09144 20 1.25634 20C1.42123 20 1.5845 19.9673 1.73671 19.9039C1.88892 19.8405 2.02708 19.7476 2.14319 19.6305L10 11.7603L17.8568 19.6305C17.9729 19.7476 18.1111 19.8405 18.2633 19.9039C18.4155 19.9673 18.5788 20 18.7437 20C18.9086 20 19.0718 19.9673 19.224 19.9039C19.3762 19.8405 19.5144 19.7476 19.6305 19.6305C19.7476 19.5143 19.8405 19.3762 19.9039 19.224C19.9674 19.0717 20 18.9084 20 18.7435C20 18.5786 19.9674 18.4153 19.9039 18.2631C19.8405 18.1109 19.7476 17.9727 19.6305 17.8566L11.7612 9.99893Z" fill="currentColor"/></svg>' +
			'</button>',
	},
	baseTpl:
		'<div class="fancybox-container" role="dialog" tabindex="-1">' +
			'<div class="fancybox-bg"></div>' +
			'<div class="fancybox-inner">' +
				'<div class="fancybox-stage"></div>' +
			'</div>' +
		'</div>' +
	'</div>',

	// mobile: {
		// dblclickContent: function(current, event) {
			// return;
		// },
		// dblclickSlide: function(current, event) {
			// return;
		// }
	// },
	// clickContent: function(current, event) {
		// return;
	// },
	beforeLoad: function( instance, current ) {

		$('body').removeClass('fancybox-type-inline fancybox-type-image fancybox-type-iframe fancybox-type-ajax');

		if ( current.type === "image" ) {
			$('body').addClass('fancybox-type-image');

		}
		if ( current.type === "iframe" ) {
			$('body').addClass('fancybox-type-iframe');

		}

		if ( current.type === "inline" ) {
			$('body').addClass('fancybox-type-inline');
		}

		if ( current.type === "ajax" ) {
			$('body').addClass('fancybox-type-ajax');
		}

	},
	afterLoad: function( instance, current ) {

		if ( current.type === "ajax" ) {
			//initSwiperSku();
			//initSelectric();
			//zoomEzPlus();
			//$(current.$content).find('[data-fancybox]').removeAttr('data-fancybox')
		
		}
	},

	beforeShow: function( instance, current ) {

		$('body').addClass('fancybox-lock');
		
		if ( current.type === "image" ) {
			$('body').addClass('fancybox-disable-touch');
		}

	},

	afterShow: function( instance, current ) {

		if ( current.src === "#search-compact" ) {
			$('[name=search]').focus();
		}

		if ( current.contentSource === 'rutube' ) {

			var theme_color = $('meta[name="theme-color"]').attr('content').replace('#', '');
			
			current.$slide.find('iframe').on('load', function ($) {
				$.currentTarget.contentWindow.postMessage(
					JSON.stringify({
						type: 'player:play',
						data: {}
					}),
					'*'
				);
				$.currentTarget.contentWindow.postMessage(
					JSON.stringify({
						type: 'player:setSkinColor',
						data: {color: theme_color}
					}),
					'*'
				);
				$.currentTarget.contentWindow.postMessage(
					JSON.stringify({
						type: 'player:unMute'
					}),
					'*'
				);
			});
		}
	},
	afterClose: function( instance, current ) {

		$('body').removeClass('fancybox-lock');

		if ( current.type === "image" ) {
			$('body').removeClass('fancybox-disable-touch');
		}

		var youtube = $('.youtube-video-place iframe');
		if ( youtube.length ) {
			youtube.attr('src', youtube.attr('src').replace(/autoplay=1/, 'autoplay=0'));
		}
	},
	media : {
		rutube: {
		  matcher: /^.+rutube.ru(\/live)?\/video\/(.*\/)?([\w]+)(.*)\//,
		  params: {
			autoplay: 1,
			frameBorder: 0,
		  },
		  type: 'iframe',
		  url: '//rutube.ru/play/embed/$3',
		  thumb: '//rutube.ru/api/video/$3/thumbnail/?redirect=1',
		}
	}
}

function initFancybox() {

	if ( $('[data-fancybox]').length ) {


		$('[data-fancybox]').fancybox(fancyboxOptions);
	

		$(document).on('click', '[data-fancybox-gallery]', function(event){
			event.preventDefault();
			var data = $(this).data('fancybox-gallery');
			$.fancybox.open(data, fancyboxOptions);
		});


		/* Agree to Terms */

		$(document).on('click', '.agree, .benefits__item[href*=\"information_id=\"]', function(event){
			event.preventDefault();
			$('#modal-agree').remove();
			var element = this;
				$.ajax({
					url: $(element).attr('href'),
					type: 'get',
					dataType: 'html',
					success: function(data) {
						html  = '<div class="fancybox-is-hidden popup popup--agree" id="modal-agree">';
						html += '  <span class="popup__title">' + $(element).text() + '</span>';
						html += '  <div class="popup__form">' + data + '</div>';
						html += '</div>';

						$('body').append(html);

						$.fancybox.open($('#modal-agree'), fancyboxOptions);
					}
				});
		});

	}
}





// --------------------------------------------------------------------------
// Range
// --------------------------------------------------------------------------


function rangeSlider() {
	if ( $('.ui-range').length ) {
		function abc(n) {
			return (n + "").split("").reverse().join("").replace(/(\d{3})/g, "$1 ").split("").reverse().join("").replace(/^ /, "");
		}
		var min_price_current = $('#min_price_current').val(),
			max_price_current = $('#max_price_current').val();

		$('.ui-range__slider').ionRangeSlider({
			type: "double",
			from: min_price_current,
			to: max_price_current,
			step: 1,
			min: 0,
			max: 1000000,
			hide_min_max: true,
			hide_from_to: true,
			force_edges: true,
			grid: false,
			onFinish: function (data) {// Called then action is done and mouse is released
				sliderProducts($('.ui-range__slider'));
			}
		});

		$('.ui-range__slider').on('change', function (event) {
			event.preventDefault();

			var range = $(this),
				rangeData = range.data("ionRangeSlider"),
				rangeDataFrom = abc(range.data("from")),
				rangeDataTo = abc(range.data("to")),
				//rangeDataWallet = range.data('wallet'),
				inputFrom = range.closest('.ui-range').find('.js-filter-from'),
				inputFromPrefix = inputFrom.data('prefix'),
				inputTo = range.closest('.ui-range').find('.js-filter-to'),
				inputToPrefix = inputTo.data('prefix');

			inputFrom.val(inputFromPrefix + ' ' + rangeDataFrom);
			inputTo.val(inputToPrefix + ' ' + rangeDataTo);
			$("#min_price_current").val(range.data("from"));
			$("#max_price_current").val(range.data("to"));
		});


		$('input[name^=\'filter\']').on('click', function(e) { 
			sliderProducts($(this));
		});
	}
}


// --------------------------------------------------------------------------
// Countdown
// --------------------------------------------------------------------------

function countdown() {

	if ( $('.js-countdown').length ) {

			$('.js-countdown').each(function(){
				var label_text = $(this).data('text-countdown') || 'Дней,Часов,Минут,Секунд';
			
			$(this).countDown({
				css_class: 'countdown',
				always_show_days: true,
				with_labels: true,
				with_seconds: false,
				with_separators: false,
				with_hh_leading_zero: false,
				with_mm_leading_zero: false,
				with_ss_leading_zero: false,
				label_dd: label_text.split(',')[0],
				label_hh: label_text.split(',')[1],
				label_mm: label_text.split(',')[2],
				label_ss: label_text.split(',')[3]
			});
		});

	}

}



function zoomEzPlus() {

	if ( $('[data-zoom]').length ) {

		$('[data-zoom-inner]').ezPlus({
			zoomType: 'inner',
			cursor: 'pointer',
			borderSize: 0,
			borderColour: '#3B55E6',
			responsive: true,
			easing: false,
			zoomWindowFadeIn: 400,
			zoomWindowFadeOut: 0,
			containLensZoom: false,
			gallery: false,
			imageCrossfade: true,
			debug: true,
			
			preloading: 0,
			
			zoomContainerAppendTo: '.app'
		});


		$('[data-zoom-lens]').ezPlus({
			zoomType: 'lens',
			cursor: 'pointer',
			borderSize: 0,
			borderColour: '#3B55E6',
			responsive: true,
			easing: false,
			zoomWindowFadeIn: 400,
			zoomWindowFadeOut: 0,
			containLensZoom: true,
			gallery: true,
			imageCrossfade: true,
			lensShape: 'square',
			zoomContainerAppendTo: '.app'
		});

		$('[data-zoom-window]').ezPlus({
			zoomType: 'window',
			cursor: 'pointer',
			borderSize: 0,
			borderColour: '#3B55E6',
			responsive: true,
			easing: false,
			zoomWindowFadeIn: 400,
			zoomWindowFadeOut: 0,
			containLensZoom: false,
			gallery: false,
			imageCrossfade: true,
			zoomContainerAppendTo: '.app'
		});

	}

}


// --------------------------------------------------------------------------
// Map
// --------------------------------------------------------------------------
/* 
window.initMap = function (){
	
	var contactsMap = new google.maps.Map(document.getElementById('map'), {
		center: { lat: 55.715423569022, lng: 37.64601299999998 },
		zoom: 15,
		mapTypeControl: false,
		disableDefaultUI: true,
	});

	// Add marker

	var icon = {
		// url: 'images/icon-location.svg',
		size: new google.maps.Size(54, 54),
		origin: new google.maps.Point(0, 0),
		anchor: new google.maps.Point(14, 54)
	};

	var contactsMarker = new google.maps.Marker({
		map: contactsMap,
		position: new google.maps.LatLng(55.715423569022,37.64601299999998),
		icon: icon,
	});
}

function loadScriptMap() {

	if ( $('#map').length ) {
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = 'https://maps.googleapis.com/maps/api/js'+'?callback=initMap';
		document.body.appendChild(script);
	}
}

*/
// --------------------------------------------------------------------------
// Lazyload
// --------------------------------------------------------------------------

$('html').addClass('is-loaded');

// --------------------------------------------------------------------------
// Add Subscribe footer
// --------------------------------------------------------------------------

function addSubscribe() {
	
	$('input[name=\'emailsubscr\']').keypress(function(e){ 
		if(e.which == 13){
			$(this).next().click();
		}
	});	
	
	$('.js-subscribe-btn').on('click', function(){
	var email = $(this).closest('.ui-group').find('input[name="emailsubscr"]').val();

	$.ajax({
		url: 'index.php?route=extension/module/prostore_subscribe/addsubscribe',
		type: 'post',
		dataType: 'json',
		data: 'email='+email+'&module=0',
		success: function (data) {
			$('.alert').remove();
			if (data['error']) {
				$('.main').prepend($('<div class="alerts-wrapper"><div class="alert alert--red"><button class="alert__close"><svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text"> ' + data['error'] + ' </p></div></div>'));
				
			}
			if (data['success']) {
				sendMetrics('prostore_subscribe');
				$('.main').prepend($('<div class="alerts-wrapper"><div class="alert alert--green"><button class="alert__close"><svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text"> ' + data['success'] + ' </p></div></div>'));
				alertAutoClose();
				$('input[name=\'emailsubscr\']').val('');
				setTimeout(function(){
					$('.alert').remove();
				}, 5000)
			}
		}
	}); 

	});
}

// --------------------------------------------------------------------------
// Datepicker
// --------------------------------------------------------------------------

function activateDatepicker() {

	if ($('body').is('.bootstrap-5')) {
		if (!$('head > script[src="catalog/view/javascript/prostore/datetimepicker/moment.min.js"]').length) {
		$('head').append('<script type="text/javascript" src="catalog/view/javascript/prostore/datetimepicker/moment.min.js"></script><script type="text/javascript" src="catalog/view/javascript/prostore/datetimepicker/moment-with-locales.min.js"></script><script type="text/javascript" src="catalog/view/javascript/prostore/datetimepicker/daterangepicker.js"></script><link href="catalog/view/javascript/prostore/datetimepicker/daterangepicker.css" type="text/css" rel="stylesheet">');
		}
		$('.date').daterangepicker({
			singleDatePicker: true,
			autoApply: true,
			locale: {
				format: 'YYYY-MM-DD'
			}
		});
		$('.time').daterangepicker({
			singleDatePicker: true,
			datePicker: false,
			autoApply: true,
			timePicker: true,
			timePicker24Hour: true,
			locale: {
				format: 'HH:mm'
			}
		}).on('show.daterangepicker', function (ev, picker) {
			picker.container.find('.calendar-table').hide();
		});
		$('.datetime').daterangepicker({
			singleDatePicker: true,
			autoApply: true,
			timePicker: true,
			timePicker24Hour: true,
			locale: {
				format: 'YYYY-MM-DD HH:mm'
			}
		});
	} else {
		if (!$('head > script[src="catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js"]').length) {
		$('head').append('<script type="text/javascript" src="catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js"></script><script type="text/javascript" src="catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js"></script><script type="text/javascript" src="catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js"></script><link href="catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet">');
		}
		
		var localeCode = document.documentElement.lang
		$('.date').datetimepicker({
			language: localeCode,
			widgetParent: '.ui-group--date',
			direction: 'bottom',
			pickTime: false
		});
		$('.datetime').datetimepicker({
			language: localeCode,
			widgetParent: '.ui-group--datetime',
			direction: 'bottom',
			pickDate: true,
			pickTime: true
		});
		$('.time').datetimepicker({
			language: localeCode,
			widgetParent: '.ui-group--time',
			direction: 'bottom',
			pickDate: false
		});
	}
}


// --------------------------------------------------------------------------
// UploadBtn
// --------------------------------------------------------------------------

function activateUploadBtn() {
	$('button[id^=\'button-upload\']').on('click', function() {
		var node = this;

		$('#form-upload').remove();

		$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');

		$('#form-upload input[name=\'file\']').trigger('click');

		if (typeof timer != 'undefined') {
			clearInterval(timer);
		}

		timer = setInterval(function() {
			if ($('#form-upload input[name=\'file\']').val() != '') {
				clearInterval(timer);

				$.ajax({
					url: 'index.php?route=tool/upload',
					type: 'post',
					dataType: 'json',
					data: new FormData($('#form-upload')[0]),
					cache: false,
					contentType: false,
					processData: false,
					beforeSend: function() {
						$(node).button('loading');
					},
					complete: function() {
						$(node).button('reset');
					},
					success: function(json) {
						$('.ui-error').remove();

						if (json['error']) {
							$(node).parent().find('input').after('<span class="error ui-error">' + json['error'] + '</span>');
						}

						if (json['success']) {
							alert(json['success']);

							$(node).parent().find('input').val(json['code']);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		}, 500);
	});
}


// --------------------------------------------------------------------------
// Show/hide additional buttons in cart depend on empty cart or not
// --------------------------------------------------------------------------

function cartExrtaElem(total) {
	if (total) {
		$('#cart  .cart__clear').show();
		$('#cart  .cart__foot').show();
	}else{
		$('#cart  .cart__clear').hide();
		$('#cart  .cart__foot').hide();
	}
}


// --------------------------------------------------------------------------
// products POP-UP view
// --------------------------------------------------------------------------

$(document).on('click','.js-btn-preview', function(e){
	e.preventDefault();
	var product_id = $(this).attr('data-for');

	$('#popupprod').removeClass('popup popup--option-required').addClass('popup--prod');

	$.ajax({
		url: 'index.php?route=product/product/show_in_popup&prod_id='+product_id+'&popup=1',
		type: 'get',
		dataType: 'html',
		success: function(html) {
			var content = $('<div />').html(html).find('.sku__view');
			$('#popupprod').html(content);
			
			activateElements();
			initSwiperSku();
			initFancybox();
			activateUploadBtn();
			if ($('#popupprod').find('input[name^=\'option\']').is('.date, .datetime, .time')) { 
				activateDatepicker();
			}
			$('#popupprod').find('#button-cart').attr('onclick', 'cart.popupadd('+ product_id +');');
			
			$.fancybox.open($('#popupprod'), fancyboxOptions);
			
			$('#popupprod').parent().addClass('fancybox-popupprod');
		}
	});
});

// --------------------------------------------------------------------------
// optionRequired POP-UP view
// --------------------------------------------------------------------------

function optionRequired(product_id) {
	$('#popupprod').removeClass('popup--prod').addClass('popup popup--option-required');
	$('#popupprod .js-sku-view').empty().html('<img src="catalog/view/theme/prostore/images/loader.svg" alt="">').load('index.php?route=product/product/show_in_popup&prod_id='+product_id+'&popup=1 #product',function(){
		$('#popupprod .sku__group:not(.sku__group--options)').addClass('hide');
		initFancybox();
		activateUploadBtn();
		if ($(this).find('input[name^=\'option\']').is('.date, .datetime, .time')) { 
			activateDatepicker();
		}
		$('#popupprod').find('.ui-btn--fullwidth').attr('onclick', 'cart.popupadd('+ product_id +');');
	});		
	
	$.fancybox.open($('#popupprod'), fancyboxOptions);
}

// --------------------------------------------------------------------------
// Opencart Filter
// --------------------------------------------------------------------------

function doFilter() {
	$(document).on('click','#button-filter',function() {
		location = getOcFilterUrl();
	});
	$(document).on('click', '.js-filter-reset', function(e) {
		location = $('input[name=\'fix_filter_action\']').val();
	});		
}

function getOcFilterUrl() {
		var filter = [];

		$('input[name^=\'filter\']:checked').each(function(element) {
			filter.push(this.value);
		});
		var min_price = $('#min_price_current').val();
		var max_price = $('#max_price_current').val();

		var url = $('input[name=\'fix_filter_action\']').val() + filter.join(',') + '&min_price=' + min_price + '&max_price=' + max_price ;
		return url ;
}

function sliderProducts(t) {
		$(".slidproducts").remove();
		$('#button-filter').removeAttr('disabled');
		var	filter = [];
		var $el = t.closest("label.ui-check");// Position of balun for checkbox
		if (t.hasClass("ui-range__slider")) {
			$el = t.closest(".filter-aside__group-body"); // Position of balun for priceslider
		}

		$('input[name^=\'filter\']:checked').each(function(element) {
			filter.push(this.value);
		});
		var min_price = $('#min_price_current').val();
		var max_price = $('#max_price_current').val();
		var cat_id = $('#filter_category_id').val();

		var url = getOcFilterUrl();
		$.ajax({
			url: 'index.php?route=product/category/totalproducts/'+ '&filter=' + filter.join(',') + '&min_price=' + min_price + '&max_price=' + max_price   + '&filter_category_id=' + cat_id,
			dataType: 'json',
			success: function (json) {
				var balun ;
				if(json['total']){
					balun = '<span class="products-amount slidproducts is-xl-visible" id="count-'+json['id']+'"><span class="products-amount__amount"> '+json['text_products']+' '+ json['total']+' </span><a class="ui-link ui-link--blue ui-link--underline" href="'+url+'">'+json['text_show']+'</a></span>';
					$('.filter-aside__more').html('<a href="'+url+'" class="ui-btn ui-btn--60 ui-btn--primary ui-btn--fullwidth">'+json['text_products']+' '+ json['total']+', '+json['text_show'].toLowerCase()+'</a>');

				}else{
					balun = '<span class="products-amount slidproducts is-xl-visible" id="count-'+json['id']+'"><span class="products-amount__amount"> '+json['text_products']+' </span>'+ json['total'];
					$('.filter-aside__more').html('<button class="ui-btn ui-btn--60 ui-btn--primary ui-btn--fullwidth">'+json['text_products']+' '+ json['total']+'</button>');
					$('#button-filter, .filter-aside__more button').attr('disabled','disabled');
				}

				$el.before($(balun).fadeIn(100));	
				setTimeout(function(){
					$("#count-"+json['id']).fadeOut(100);
				}, 6000)
			}	
		});
}


// --------------------------------------------------------------------------
// products POP-UP view
// --------------------------------------------------------------------------

$(document).on('click', '.js-button-fast-cart', function(e) {
	var product_id = $(this).attr('data-for');
	var qty = $(this).closest('.sku__action').find('.ui-number__input input').val();
	if (qty == undefined) {
		qty = 1;
	}
	var type = $(this).attr('data-typefrom');

	$('#cat_qty').val(qty);
	$('#cat_prod_id').val(product_id);
	$('#buy-click-type').val(type);
});


$(document).on('click','.quickbuy-send', function(e){

	if ($('#buy-click-type').val() == 'category-popup') {
		$('.fast-redirect').val(1);
		var $data = $('#popup-buy-click input,#popup-buy-click textarea,#popupprod input[type=\'text\'], #popupprod input[type=\'hidden\'], #popupprod input[type=\'radio\']:checked, #popupprod input[type=\'checkbox\']:checked, #popupprod select, #popupprod textarea');
	}else{
		var $data = $('#popup-buy-click input,#popup-buy-click textarea,  #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product input[type=\'text\']:not([name=\'quantity\']), #product select, #product textarea');
	}

	cart.add2cartFast($data);			
});	

$(document).on('click', '.js-fancy-popup-cart', function(e) {
	var eventtag = 'prostore_buyclick';
	sendMetrics(eventtag);
});


// --------------------------------------------------------------------------
// Yandex GA Metric
// --------------------------------------------------------------------------

function sendYM(event){
	if (typeof (ym) === "function") { // If YandexMetric is turned on in admin's pannel
		if($("meta[property='yandex_metric']").attr('content')){
			var yandex_metric = $("meta[property='yandex_metric']").attr('content');
			ym(yandex_metric, 'reachGoal', event);
		}
	}
}

function sendMetrics(eventname){ 
	if (typeof (gtag) === "function") { // If Google Metric is turned on in admin's pannel 
		gtag('event', eventname, {});
	}	
	if (typeof (ym) === "function") { // If YandexMetric is turned on in admin's pannel 
		if($("meta[property='yandex_metric']").attr('content')){
			var yandex_metric = $("meta[property='yandex_metric']").attr('content');
			ym(yandex_metric, 'reachGoal', eventname);
		}
	}		
}

function sendEcommerceYandexMetrica(data) {
	if (data.event == 'purchase') {
		dataLayer.push({
			"ecommerce":{
				"currencyCode": data.analystdata.currency,
				"purchase": {
					"actionField": {
						"id": data.analystdata.transaction_id,
					},
					"products": data.analystdata.items
				}
			}
		});
	}else{
		dataLayer.push({
			"ecommerce": {
				"currencyCode": data.analystdata.currency,
				"add": {
					"products": data.analystdata.items
				}
			}
		});
	}
}

function sendGA(datapr,event){

		if (typeof (gtag) !== "function" && typeof (ym) !== "function") { // If GA and YA are turned off in admin's pannel, return 
			return;
		}
		$.ajax({
			url: 'index.php?route=product/product/analystdata',
			dataType: "json",
			type: "POST",
			data: datapr,
			success: function(item){
				if(!!item.items){
					if (typeof (gtag) === "function") { // If Google Metric is turned on in admin's pannel
						gtag('event', event, item);
					}else{
						sendEcommerceYandexMetrica({
							event: event,
							analystdata: item
						});							
					}
				}
			}
		});
}

function sendGAch(datapr,event){ 
		if (typeof (gtag) !== "function" && typeof (ym) !== "function") { // If GA is turned off in admin's pannel, return 
			return;
		}
		$.ajax({
				url: 'index.php?route=product/product/analystdataorder',
				dataType: "json",
				type: "POST",
				data: datapr,
				success: function(data){ 
					setTimeout(function(){
						if (typeof (gtag) === "function") { // If Google Metric is turned on in admin's pannel
							gtag('event', event, data);
						}else{
							sendEcommerceYandexMetrica({
								event: event,
								analystdata: data
							});
						}
					},100);
				}
		});
}

// --------------------------------------------------------------------------
// Adult
// --------------------------------------------------------------------------

function adult() {
	if($('#popup-age').length){
		$.fancybox.open({
			src  : '#popup-age',
			type : 'inline',
			opts : {
				autoFocus : false,
				scrolling   : 'no',
				clickSlide : false,
				buttons : false,
				touch : false,
				modal: true,
				afterClose: function( instance, current ) {
					$('body').removeClass('fancybox-age');
				}
			}			
		});
		$('#popup-age').on('click', '.js-adult', function(e) { //console.log('setadult');
		  var date = new Date(new Date().getTime() + 1000 * 60 * 60 * 24 * 365);
		  document.cookie = "899_is_adult=1; path=/; SameSite=Lax; expires=" + date.toUTCString();
		});
	}
}

// --------------------------------------------------------------------------
// Share
// --------------------------------------------------------------------------

$(document).on('click','.js-share', function(e){
	e.preventDefault();
	if ($('#share .popup__form').is(':empty')) {
		var ys2 = document.createElement('script');
		ys2.type = 'text/javascript';
		ys2.async = true;
		ys2.src = '//yastatic.net/share2/share.js';
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(ys2, s);
		$('#share .popup__form').html('<div class="ya-share2" data-curtain data-size="l" data-shape="round" data-limit="6" data-services="vkontakte,odnoklassniki,telegram,twitter,viber,whatsapp,messenger,moimir,skype,tumblr,lj"></div>');
	}
	$.fancybox.open($('#share'), fancyboxOptions);
});



// --------------------------------------------------------------------------
// h1
// --------------------------------------------------------------------------

//$('#content:has(h1)').each(function(e) {
//	$("#content h1").insertBefore(".breadcrumb").addClass('breadcrumbs__title')
//});


// Cart add remove functions
var cart = {
	'add': function(product_id, quantity) {
		var datapr = 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1);
		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: datapr,
			dataType: 'json',
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(json) {
				$('.alerts-wrapper, .text-danger').remove();

				if (json['redirect']) {
					//location = json['redirect'];
					optionRequired(product_id);
				}

				if (json['success']) {

					// Need to set timeout otherwise it wont update the total
					setTimeout(function () {
						$('.js-cart-total').html('<span id="cart-total">' + json['total'] + '</span>');
					}, 100);

					cartExrtaElem(json['total']);
					
					if ($('.js-cart-call').length && window.matchMedia('(min-width: 1199px)').matches) {
						$('.js-cart-call').trigger('click');
					} else {
						$('main').prepend($('<div class="alerts-wrapper"><div class="alert alert--green"><button class="alert__close"><svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text"> ' + json['success'] + ' </p></div></div>'));
						alertAutoClose();
					}

					toCartButtonCommon(product_id);
					
					sendYM('prostore_addtocart_catalog');
					sendGA(datapr,'prostore_addtocart_catalog');

					$.ajax({
						url: 'index.php?route=common/cart/info',
						dataType: 'html',
						success: function(data) {
							$('#cart .header__cart-load').html($(data).find('.header__cart-offcanvas'));
								hasScrollBar();
								$('[data-fancybox]').fancybox(fancyboxOptions);
							}
					});
					
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'popupadd': function(product_id) { // Add products to cart from PopUP Window

			var datas = $('#popupprod input[type=\'text\'], #popupprod input[type=\'hidden\'], #popupprod input[type=\'number\'], #popupprod input[type=\'radio\']:checked, #popupprod input[type=\'checkbox\']:checked, #popupprod select, #popupprod textarea');
			$.ajax({
				url: 'index.php?route=checkout/cart/add',
				type: 'post',
				data: datas,
				dataType: 'json',
				beforeSend: function() {
					$('.js-btn-add-cart').attr('disabled', 'disabled');
				},
				complete: function() {
					$('.js-btn-add-cart').removeAttr('disabled');
				},
				success: function(json) {
					$('.alert,#popupprod .ui-error').remove();
					$('#popupprod [id^="input-option"],#popupprod .ui-field').removeClass('is-error');

					if (json['error']) {

						if (json['error']['option']) {
							for (i in json['error']['option']) {
								var element = $('#popupprod #input-option' + i.replace('_', '-'));
								
								if (element.parent().hasClass('ui-select')) {
									element.parent().after('<span class="error ui-error">' + json['error']['option'][i] + '</span>').parent().addClass('is-error');
								} else if (element.hasClass('ui-input') || element.hasClass('ui-textarea')) {
									element.after('<span class="error ui-error">' + json['error']['option'][i] + '</span>').parent().addClass('is-error');
								} else {
									element.after('<span class="error ui-error">' + json['error']['option'][i] + '</span>').addClass('is-error');
								}
							}
						}

						if (json['error']['recurring']) {
							$('#popupprod select[name=\'recurring_id\']').parent().after('<span class="error ui-error">' + json['error']['recurring'] + '</span>').parent().addClass('is-error');
						}
						
					}
					if (json['success']) {						
						$.fancybox.close();
						cartExrtaElem(json['total']);
						
						if ($('.js-cart-call').length && window.matchMedia('(min-width: 1199px)').matches) {
							$('.js-cart-call').trigger('click');
						} else {
							$('main').prepend($('<div class="alerts-wrapper"><div class="alert alert--green"><button class="alert__close"><svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text"> ' + json['success'] + ' </p></div></div>'));
							alertAutoClose();
						}
						
						$('.js-cart-total').html('<span id="cart-total">' + json['total'] + '</span>');

						toCartButtonCommon(product_id);
						
						//sendYM('prostore_addtocart_product');
						//sendGA(datas,'prostore_addtocart_product');
						
						$.ajax({
							url: 'index.php?route=common/cart/info',
							dataType: 'html',
							success: function(data) {
								$('#cart .header__cart-load').html($(data).find('.header__cart-offcanvas'));
									hasScrollBar();
									$('[data-fancybox]').fancybox(fancyboxOptions);
								}
						});
					}
					
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
	},
	'add2cartFast': function($data) {

	      $.ajax({
	        url: 'index.php?route=extension/module/prostore/prostorecart/fastadd2cart',
	        type: 'post',
	        data: $data,
	        dataType: 'json',
	        beforeSend: function() {
	          $('.quickbuy-send').attr('disabled', 'disabled');
	          //$('.alert, .product-page__input-box-error, .popup-simple__inner-error-text').remove();
	          $('.alert, .ui-error, .icon-error').remove();
	          $('.ui-group, .ui-field').removeClass('is-error');
	        },
	        complete: function() {
	          $('.quickbuy-send').removeAttr('disabled');
	        },
	        success: function(json) {
			$('.alert, .ui-error').remove();
			$('[id^="input-option"],.ui-field,.ui-select').removeClass('is-error');

	          if (json['error']) { 
	            if (json['redirect']) {
	                      
	              setTimeout(function() { $.fancybox.close() }, 2000);
	              setTimeout(function() { location = json['redirect']; }, 3000);

	            }
				
	            if (json['error']['option']) {
	              for (i in json['error']['option']) {
	                var element = $('#input-option' + i.replace('_', '-'));
	                
					if (element.parent().hasClass('ui-select')) {
						element.parent().after('<span class="error ui-error">' + json['error']['option'][i] + '</span>').parent().addClass('is-error');
					} else if (element.hasClass('ui-input') || element.hasClass('ui-textarea')) {
						element.after('<span class="error ui-error">' + json['error']['option'][i] + '</span>').parent().addClass('is-error');
					} else {
						element.after('<span class="error ui-error">' + json['error']['option'][i] + '</span>').addClass('is-error');
					}
	              }
				  if (typeof json['error']['popup'] === 'undefined') {
				  
						var error_text = '';
						for (i in json['error']['option']) { error_text += json['error']['option'][i] + ' '; }

						$('#popup-buy-click .ui-btn').before('<div class="alert alert--red alert--opacity"><button class="alert__close"> <svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text">' + error_text + '</p></div>');
						setTimeout(function(){
						$('.alert-danger').remove();
						}, 2000);                     
						setTimeout(function() { 
							
							$.fancybox.close();
							
							tab_target = $('.sku__settings .error.ui-error').eq(0).offset().top - 80;

							if (window.matchMedia('(min-width: 1199px)').matches) {
								if ( $('body').is('.is-page-header-fixed') ) {
									tab_target -= $('.header-fixed').data('fixed-height') || $('.header-fixed').innerHeight();
								}
							} else {
								tab_target = tab_target - $('.header__mobile-fixed').innerHeight();
							}
							
							$('html, body').animate({
								scrollTop: tab_target
							}, 'fast');
						
						}, 1500);	
						
				  }
	            }

	            if (json['error']['error_stock']) {

	              $('#popup-buy-click .ui-btn').before('<div class="alert alert--red alert--opacity"><button class="alert__close"> <svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text">' + json['error']['error_stock'] + '</p></div>');
	                setTimeout(function(){
	                  $('.alert-success').remove();
	                }, 2000);                     
	              setTimeout(function() { $.fancybox.close() }, 1500);
	  
	            }

	            if (json['error']['error_min_warning']) {                	  
	                $('#popup-buy-click .ui-btn').before('<div class="alert alert--red alert--opacity"><button class="alert__close"> <svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text">' + json['error']['error_min_warning'] + '</p></div>');                   	  
	            }

	            if (json['error']['recurring']) {
	              $('select[name=\'recurring_id\']').parent().after('<span class="error ui-error">' + json['error']['recurring'] + '</span>').parent().addClass('is-error');
	              $.fancybox.close();
	            }
	            if (json['error']['popup']) {
	              if (json['error']['popup']['name']) {
	                $('.popup--buy-click input[name=\'name\']').after('<span class="error ui-error">' + json['error']['popup']['name'] + '</span>').addClass('is-error');
	              }
	              if (json['error']['popup']['phone']) {
	                $('.popup--buy-click input[name=\'phone\']').after('<span class="error ui-error">' + json['error']['popup']['phone'] + '</span>').addClass('is-error');
	              } 
	              if (json['error']['popup']['email']) {
	                $('.popup--buy-click input[name=\'email\']').after('<span class="error ui-error">' + json['error']['popup']['email'] + '</span>').addClass('is-error');
	              } 
	              if (json['error']['popup']['comment']) {
	                $('.popup--buy-click textarea[name=\'comment\']').after('<span class="error ui-error">' + json['error']['popup']['comment'] + '</span>').addClass('is-error');
	              } 
				  if (json['error']['popup']['captcha']) { 
				  	$('.popup--buy-click input[name=\'captcha\'], .popup--buy-click .js-captcha').after('<span class="error ui-error">' + json['error']['popup']['captcha'] + '</span>');
				  }
	            }
	          }
	          if (json['success']) {
	            sendMetrics('prostore_buyclick_success');

	            $.fancybox.close();
				
				$('popup--buy-click input,popup--buy-click textarea').val('');

	            location = json['redirect'];
	          }
	          
	        },
	        error: function(xhr, ajaxOptions, thrownError) {
	          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
	      }); 
	},
	'update': function(key, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: key +'=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('.js-cart-total').html('<span id="cart-total">' + json['total'] + '</span>');
				}, 100);
				
				cartExrtaElem(json['total']);

				if ( json['deleted_items'] != undefined && json['deleted_items'].length) {
					for (let index = 0; index < json['deleted_items'].length; index++) {
						toCartButtonCommonReset(json['deleted_items'][index]);
						toCartButtonReset(json['deleted_items'][index]);
					}
				}

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout' || $('.cart__content').length) {  
					location = 'index.php?route=checkout/cart';
				} else {
					$.ajax({
						url: 'index.php?route=common/cart/info',
						dataType: 'html',
						success: function(data) {
							$('#cart .header__cart-load').html($(data).find('.header__cart-offcanvas'));
								hasScrollBar();
								$('[data-fancybox]').fancybox(fancyboxOptions);
							}
					});			
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
					setTimeout(function () {
						$('.js-cart-total').html('<span id="cart-total">' + json['total'] + '</span>');
					}, 100);

				cartExrtaElem(json['total']);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout' || $('.cart__content').length) {
					location = 'index.php?route=checkout/cart';
				} else {

					initCartButtons(json);

					$.ajax({
						url: 'index.php?route=common/cart/info',
						dataType: 'html',
						success: function(data) {
							$('#cart .header__cart-load').html($(data).find('.header__cart-offcanvas'));
								hasScrollBar();
								$('[data-fancybox]').fancybox(fancyboxOptions);
							}
					});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'clear': function() {
		$.ajax({
			url: 'index.php?route=checkout/cart/clear',
			type: 'post',
//			data: 'key=' + 1,
			dataType: 'json',
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(json) {
				
				setTimeout(function () {
					$('.js-cart-total').html('<span id="cart-total">' + json['total'] + '</span>');
				}, 100);
				
				cartExrtaElem(0);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout' || $('.cart__content').length) {
					location = 'index.php?route=checkout/cart';
				} else {

					initCartButtons(json);
					
					$.ajax({
						url: 'index.php?route=common/cart/info',
						dataType: 'html',
						success: function(data) {
							$('#cart .header__cart-load').html($(data).find('.header__cart-offcanvas'));
								hasScrollBar();
								$('[data-fancybox]').fancybox(fancyboxOptions);
							}
					});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var voucher = {
	'add': function() {

	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
						if (json['total'] > 0) {
						$('#cart-total').html('<mark class="cart__counter" id="cart-total"> ' + json['total'] + '</mark>');
						} else {
						$('#cart-total').html('');
						}
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout' || $('.cart__content').length) {
					location = 'index.php?route=checkout/cart';
				} else {
					$.ajax({
						url: 'index.php?route=common/cart/info',
						dataType: 'html',
						success: function(data) {
							$('#cart .header__cart-load').html($(data).find('.header__cart-offcanvas'));
								hasScrollBar();
								$('[data-fancybox]').fancybox(fancyboxOptions);
							}
					});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			complete: function() {
				getCompareWish();
			},
			success: function(json) {
				$('.alerts-wrapper').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				var elem = $('.products__item, #product').find("button[data-for='"+product_id+"']").filter("[data-action='wishlist']");

				if(json['error'] != 1){
					elem.addClass('is-active');
					var svg_elem = elem.find('svg');
					$('#product .sku__addto .sku__addto-btn[data-action="wishlist"] .sku__addto-btn-text').html(json['button_text']);				
					svg_elem.attr('class',"icon-favorites-active").find('use').attr('xlink:href',"catalog/view/theme/prostore/sprites/sprite.svg#icon-favorites-active");	
				}

				if (json['success']) {
					$('main').prepend($('<div class="alerts-wrapper"><div class="alert alert--green"><button class="alert__close"><svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text"> ' + json['success'] + ' </p></div></div>'));
					alertAutoClose();
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/remove',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			complete: function() {
				getCompareWish();
			},
			success: function(json) {
				$('.alerts-wrapper').remove();

				var elem = $('.products__item, #product').find("button[data-for='"+product_id+"']").filter("[data-action='wishlist']");

				if(json['error'] != 1){
					elem.removeClass('is-active');
					var svg_elem = elem.find('svg');
					$('#product .sku__addto .sku__addto-btn[data-action="wishlist"] .sku__addto-btn-text').html(json['button_text']);				
					svg_elem.attr('class',"icon-favorites").find('use').attr('xlink:href',"catalog/view/theme/prostore/sprites/sprite.svg#icon-favorites");	
				}			

				if (json['success']) {
					$('main').prepend($('<div class="alerts-wrapper"><div class="alert alert--green"><button class="alert__close"><svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text"> ' + json['success'] + ' </p></div></div>'));
					alertAutoClose();
				}				
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			complete: function() {
				getCompareWish();
			},
			success: function(json) {
				$('.alerts-wrapper').remove();

				var elem = $('.products__item, #product').find("button[data-for='"+product_id+"']").filter("[data-action='compare']");

				if(json['error'] != 1){
					elem.addClass('is-active');
					var svg_elem = elem.find('svg');
					svg_elem.attr('class',"icon-compare-active").find('use').attr('xlink:href',"catalog/view/theme/prostore/sprites/sprite.svg#icon-compare-active");	
				}				

				if (json['success']) {
					$('main').prepend($('<div class="alerts-wrapper"><div class="alert alert--green"><button class="alert__close"><svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text"> ' + json['success'] + ' </p></div></div>'));
					alertAutoClose();
					$('#product .sku__addto .sku__addto-btn[data-action="compare"] .sku__addto-btn-text').html(json['button_text']);
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/remove',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			complete: function() {
				getCompareWish();
			},
			success: function(json) {
				$('.alerts-wrapper').remove();

				var elem = $('.products__item, #product').find("button[data-for='"+product_id+"']").filter("[data-action='compare']");

				if(json['error'] != 1){
					elem.removeClass('is-active');
					var svg_elem = elem.find('svg');
					svg_elem.attr('class',"icon-compare").find('use').attr('xlink:href',"catalog/view/theme/prostore/sprites/sprite.svg#icon-compare");	
				}					

				if (json['success']) {
					$('main').prepend($('<div class="alerts-wrapper"><div class="alert alert--green"><button class="alert__close"><svg class="icon-close-alerts"><use xlink:href="catalog/view/theme/prostore/sprites/sprite.svg#icon-close-alerts"></use></svg></button><p class="alert__text"> ' + json['success'] + ' </p></div></div>'));
					alertAutoClose();
					$('#product .sku__addto .sku__addto-btn[data-action="compare"] .sku__addto-btn-text').html(json['button_text']);
				}				
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var comment = {
	'add': function(blog_id) {
		$.ajax({
			url: 'index.php?route=extension/module/prostore_blog/write&blog_id=' + blog_id,
			type: 'post',
			dataType: 'json',
			data: $("#form-comment").serialize(),
			beforeSend: function() {
				$('#form-comment button').button('loading');
			},
			complete: function() {
				$('#form-comment button').button('reset');
				setTimeout(function(){
					$('.alert').remove();
				}, 5000);
			},
			success: function(json) {
				$('.alert--red').remove();
				if (json['error']) {
					$('#form-comment').before('<div class="alert alert--red alert--opacity"><p class="alert__text">' + json['error'] + '</p></div>');
				}
				if (json['success']) {
					$('#form-comment').before('<div class="alert alert--green alert--opacity"><p class="alert__text">' + json['success'] + '</p></div>');
					$('input[name=\'name\']').val('');
					$('textarea[name=\'text\']').val('');
					$('input[name=\'email\']:checked').prop('checked', false);
				}
				if (json['redirect']) {
					document.location.reload();
				}
			}
		});
	},
}


$(function() {

	// --------------------------------------------------------------------------
	// Init
	// --------------------------------------------------------------------------

	cookieagry();
	scrollToTop();
	currlanguage();
	callBack();
	initPriorityNav();
	headerMobileSticky();
	headerTriggers('js-nav-trigger', 'is-nav-open');
	headerTriggers('js-call-trigger', 'is-call-open');
	headerTriggers('js-currency-trigger', 'is-currency-open');
	headerTriggers('js-language-trigger', 'is-language-open');
	headerTriggers('js-catalog-trigger', 'is-catalog-open');
	headerTriggers('js-search-trigger', 'is-search-open');
	initSwiperSku();
	stickySku();
	stickyPersonal();
	toggle();
	initReadmore();
	compactSku();
	initFancybox();
	rangeSlider();
	doFilter();
	countdown();
	zoomEzPlus();
	hasScrollBar();
	SearchInput();
	initSwiper();
	addSubscribe();
	GalleryMouseover();
	Categories();
	adult();
	cssVars(); // IE Suport Css Vars
	bootstrapTooltip();
	categoryViewOC();
});
