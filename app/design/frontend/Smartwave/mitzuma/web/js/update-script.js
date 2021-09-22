require(['jquery', 'mage/translate'], function($, $t){

	$(document).ready(function(){
        var myInterval = setInterval(() => {
            if ($('.logged-in').length > 0 && $('.not-logged-in').length>0){

            }else{
                if ($('.logged-in').length > 0) {
                    $('body').addClass('logged');
                }else{
                    $('body').removeClass('logged');
                }
                $('.header-main-right .custom-block').css('opacity', 1);
                clearInterval(myInterval);
            }
        }, 200);
        if ($('.form-wishlist-items').length > 0) {
            var t=0; // the height of the highest element (after the function runs)
            var t_elem;  // the highest element (after the function runs)
            $(".products-grid.wishlist .product-item-name").each(function () {
                $this = $(this);
                if ( $this.outerHeight() > t ) {
                    t_elem=this;
                    t=$this.outerHeight();
                }
                $this.outerHeight(t);
            });
        }
        if ($('.fieldset input.input-text:first-child').length>0) {
            $('.fieldset input.input-text:first-child').each(function(){
                $(this).parent().parent().addClass('text-field');
                if ($(this).val() != "") {
                    $(this).parent().parent().addClass('focus');
                }
                $(this).focus(function(){
                    $(this).parent().parent().addClass('focus');
                });
                $(this).blur(function(){
                    if ($(this).val() == "") {
                        $(this).parent().parent().removeClass('focus');   
                    }
                })
            })
        }

        if ($('.account-nav ul.nav.items').length>0) {
            $('.account-nav-content').prepend('<div class="account-nav-toggle">' + $t('My Profile') + '</div>');
            $('.account-nav ul.nav.items').wrapAll('<div class="account-nav-wrapper"></div>');
            $('.account-nav-toggle').click(function(){
                $(this).toggleClass('open');
                $('.account-nav-wrapper').slideToggle();
            })
            $('.account-nav-content').show();
        }
        if($('.custom-qty').length > 0){
            $('.page-main .field.qty').each(function(){
                var qty = $(this).find('input').val();
                $(this).find('select option[value='+qty+']').attr('selected','selected');
            })
            $('.custom-qty').each(function(){
                $(this).change(function(){
                    $(this).parent().find('input').val($(this).val());
                })
            })
        }
    })
	$(window).resize(function(){
        if ($('.form-wishlist-items').length > 0) {
            var t=0; // the height of the highest element (after the function runs)
            var t_elem;  // the highest element (after the function runs)
            $(".products-grid.wishlist .product-item-name").each(function () {
                $this = $(this);
                if ( $this.outerHeight() > t ) {
                    t_elem=this;
                    t=$this.outerHeight();
                }
                $this.outerHeight(t);
            });
        }
    })
	
})
