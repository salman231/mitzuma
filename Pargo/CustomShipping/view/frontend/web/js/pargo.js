require([
    'jquery',
    'domReady!',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data'


], function ($, ko, Component, quote, domReady, checkout, customer ) {

    (function(win) {
        'use strict';

        var listeners = [],
            doc = win.document,
            MutationObserver = win.MutationObserver || win.WebKitMutationObserver,
            observer;

        function ready(selector, fn) {
            // Store the selector and callback to be monitored
            listeners.push({
                selector: selector,
                fn: fn
            });
            if (!observer) {
                // Watch for changes in the document
                observer = new MutationObserver(check);
                observer.observe(doc.documentElement, {
                    childList: true,
                    subtree: true
                });
            }
            // Check if the element is currently in the DOM
            check();
        }

        function check() {
            // Check the DOM for elements matching a stored selector
            for (var i = 0, len = listeners.length, listener, elements; i < len; i++) {
                listener = listeners[i];
                // Query for elements matching the specified selector
                elements = doc.querySelectorAll(listener.selector);
                for (var j = 0, jLen = elements.length, element; j < jLen; j++) {
                    element = elements[j];
                    // Make sure the callback isn't invoked with the
                    // same element more than once
                    if (!element.ready) {
                        element.ready = true;
                        // Invoke the callback with the element
                        listener.fn.call(element, element);
                    }
                }
            }
        }

        // Expose `ready`
        win.ready = ready;

    })(this);

    ready('.opc-progress-bar',function () {

        $('.opc-progress-bar').click(function () {
            if($("input[value='pargo_customshipping_pargo_customshipping']").prop('checked') && localStorage.getItem('pargoPoint') != null) {
                $('.form-shipping-address').hide();
                $('.checkout-shipping-address').hide();
                $('.continue').attr('disabled',false);
            }
        });
    });

    ready('.action-edit',function () {

        $('.action-edit').click(function () {
            if($("input[value='pargo_customshipping_pargo_customshipping']").prop('checked') && localStorage.getItem('pargoPoint') != null) {
                $('.form-shipping-address').hide();
                $('.checkout-shipping-address').hide();
                $('.continue').attr('disabled',false);
            }
        });
    });



    ready('.checkout',function () {
        console.log('ready');
        $('.checkout').click(function () {
            console.log(checkout.getSelectedShippingRate());
        })
    });



    var pargoPointState = false;
    var loadPargoInformation = false;
    var isLoggedIn = false;

    if(window.checkoutConfig.customerData.firstname){
        isLoggedIn = true
    }

    //set pargo state
    if(localStorage.getItem('pargoPoint')){
        pargoPointState = true;
    }

    // pargo can be loaded
    if(pargoPointState === true && checkout.getSelectedShippingRate() === 'pargo_customshipping_pargo_customshipping'){
        loadPargoInformation = true;
        pargoPointState = true;
    }

    //pargo can be loaded just set the shipping rate
    if(pargoPointState === true && checkout.getSelectedShippingRate() !== 'pargo_customshipping_pargo_customshipping'){
        loadPargoInformation = true;
        pargoPointState = true;
        // checkout.setSelectedShippingRate('pargo_customshipping_pargo_customshipping');
    }


    if (window.addEventListener) {
        window.addEventListener("message", setPargoPointInformation, false);
    } else {
        window.attachEvent("onmessage", setPargoPointInformation);
    }

    function setPargoPointInformation(point) {
        if (!point.data.pargoPointCode) {
            return true;
        }

        localStorage.setItem('pargoPoint',JSON.stringify(point.data));
        $('input[value="pargo_customshipping_pargo_customshipping"]').trigger('click');
        $('.close').trigger('click');
        $('.continue').attr('disabled',false);
        var shippingAddressFromData =
            {
                city: point.data.city,
                company: point.data.storeName,
                country_id: 'ZA',
                firstname: 'Imtiyaaz',
                lastname: 'Salie',
                postcode: '7780',
                region: 'WC',
                street: {0:'dsfds',1:'',2:''},
                telephone: '035235235'
            },
            nameSelector = 'input[name=firstname]',
            lastnameSelector = 'input[name=lastname]',
            telephoneSelector = 'input[name=telephone]',
            name = $(nameSelector).val(),
            lastname = $(lastnameSelector).val(),
            telephone = $(telephoneSelector).val();

        if (name === '') {
            name = 'PARGO SHIPMENT';
        }
        if (lastname === '') {
            lastname = 'PARGO POINT-'+point.data.pargoPointCode;
        }
        if (telephone === '') {
            telephone = '000';
        }

        $('.form-shipping-address').hide();
        $('select[name=country_id]').val('ZA').change();
        $(nameSelector).val(name).change();
        $(lastnameSelector).val(lastname).change();
        $('input[name=company]').val(point.data.storeName+'-'+point.data.pargoPointCode).change();
        $('input[name="street[0]"]').val(point.data.address1).change();
        $('input[name="street[1]"]').val(point.data.address2).change();
        $('input[name="street[2]"]').val(point.data.suburb).change();
        $('input[name=city]').val(point.data.city).change();
        $('input[name=region]').val(point.data.province).change();
        $('input[name=postcode]').val(point.data.postalcode).change();
        $(telephoneSelector).val(telephone).change();
        pargoAlternativeDisplay();
    }

    ready('.pargo-btn', function () {

        if((pargoPointState === true && loadPargoInformation === true) && $('.radio:checked').val() === 'pargo_customshipping_pargo_customshipping' || checkout.getSelectedShippingRate() === 'pargo_customshipping_pargo_customshipping' && localStorage.getItem('pargoPoint') !== null){
            pargoAlternativeDisplay();
            if(isLoggedIn) {
                $('.checkout-shipping-address').hide();
                var shippingAddressFromData =
                    {
                        city: JSON.parse(localStorage.getItem('pargoPoint')).city,
                        company: JSON.parse(localStorage.getItem('pargoPoint')).storeName,
                        country_id: 'ZA',
                        firstname: 'PARGO SHIPMENT',
                        lastname: 'Salie',
                        postcode: JSON.parse(localStorage.getItem('pargoPoint')).postalCode,
                        region: JSON.parse(localStorage.getItem('pargoPoint')).province,
                        street: {0:JSON.parse(localStorage.getItem('pargoPoint')).address1 ,1:JSON.parse(localStorage.getItem('pargoPoint')).address2,2:''},
                        telephone: '0000'
                    };
                var shippingAddress =
                    {
                        city: JSON.parse(localStorage.getItem('pargoPoint')).city,
                        company: JSON.parse(localStorage.getItem('pargoPoint')).storeName,
                        country_id: 'ZA',
                        firstname: 'PARGO SHIPMENT',
                        lastname: 'Salie',
                        postcode: JSON.parse(localStorage.getItem('pargoPoint')).postalCode,
                        region: JSON.parse(localStorage.getItem('pargoPoint')).province,
                        region_id: '577',
                        street: {0:JSON.parse(localStorage.getItem('pargoPoint')).address1 ,1:JSON.parse(localStorage.getItem('pargoPoint')).address2,2:''},
                        save_in_address_book: 0,
                        telephone: '0000'
                    };

                //checkout.setSelectedShippingRate('pargo_customshipping_pargo_customshipping');
                //checkout.setSelectedShippingAddress(shippingAddress);
                // checkout.setShippingAddressFromData(shippingAddressFromData);
            }
        }
        else {
            pargoDefaultDisplay();
            if($('.radio:checked').val() !== 'pargo_customshipping_pargo_customshipping'){
                $('.form-shipping-address').show();

            }
        }

        $('.radio').change(function () {

            if($(this).val() === 'pargo_customshipping_pargo_customshipping' && isLoggedIn){
                pargoDefaultDisplay();
                $('.form-shipping-address').hide();
                $('.checkout-shipping-address').hide();
            } else {
                localStorage.removeItem('pargoPoint');
                pargoDefaultDisplay();
                $('.form-shipping-address').show();
                $('.checkout-shipping-address').show();
                $('.pargo-btn').hide();
            }


            if($(this).val() === 'pargo_customshipping_pargo_customshipping'){
                pargoDefaultDisplay();
                $('.form-shipping-address').hide();
            } else {
                localStorage.removeItem('pargoPoint');
                pargoDefaultDisplay();
                $('.form-shipping-address').show();
                $('.pargo-btn').hide();


            }

        })
    });


    function pargoDefaultDisplay() {

        var btnText = 'Select a Pargo Point';
        var btnTextColor = '#ffffff';
        var btnTextHoverColor = '#000000';
        var btnColor = '#3475B7';
        var btnHoverColor = '#3475B7';
        $('.pargo-store-info').remove();
        $('.pargo-btn').show();

        if(localStorage.getItem('pargoPoint') === null) {
            $('.form-shipping-address').hide();
            $('#pargo-point').hide();

        }


        $('.pargo-btn').text(btnText);
        $(this).css("background-color",btnHoverColor);
        $(this).css("color",btnTextColor);

        $(".pargo-btn").mouseover(function() {
            $(this).css("background-color",btnHoverColor);
            $(this).css("color",btnTextHoverColor);
        }).mouseout(function() {
            $(this).css("background-color",btnColor);
            $(this).css("color",btnTextColor);
        });

    }

    function pargoAlternativeDisplay() {

        if($('.radio:checked').val() === 'pargo_customshipping_pargo_customshipping' || checkout.getSelectedShippingRate() === 'pargo_customshipping_pargo_customshipping') {
            pargoDefaultDisplay();
        }

        var btnText = 'Change Pargo Point';
        var btnTextColor = '#000000';
        var btnTextHoverColor = '#ffffff';
        var btnColor = '#FEF051';
        var btnHoverColor = '#3475B7';
        $('.form-shipping-address').hide();
        $('.pargo-btn').show();
        $('.pargo-btn').text(btnText);
        $('.pargo-store-info').remove();
        if(localStorage.getItem('pargoPoint')) {
            $('#pargo-point').show().append('<div class="pargo-store-info"><strong>Store name</strong><p>' + JSON.parse(localStorage.getItem('pargoPoint')).storeName + '</p><br><strong>Store location</strong><p>' + JSON.parse(localStorage.getItem('pargoPoint')).address1 + ', ' + JSON.parse(localStorage.getItem('pargoPoint')).city + ', ' + JSON.parse(localStorage.getItem('pargoPoint')).postalcode + ', ' + JSON.parse(localStorage.getItem('pargoPoint')).province + ', ' + JSON.parse(localStorage.getItem('pargoPoint')).suburb + '</p></div>');
        }

        $(this).css("background-color",btnHoverColor);
        $(this).css("color",btnTextColor);

        $(".pargo-btn").mouseover(function() {
            $(this).css("background-color",btnHoverColor);
            $(this).css("color",btnTextHoverColor);
        }).mouseout(function() {
            $(this).css("background-color",btnColor);
            $(this).css("color",btnTextColor);
        });

    }

    function renderPargo() {

        if(localStorage.getItem('pargoPoint')){

            var btnText = 'Change Pargo Point';
            var btnTextColor = '#000000';
            var btnTextHoverColor = '#ffffff';
            var btnColor = '#FEF051';
            var btnHoverColor = '#3475B7';
            $('.form-shipping-address').hide();
            $('.pargo-btn').show();
            $('.pargo-store-info').remove();
            $('#pargo-point').show().append('<div class="pargo-store-info"><strong>Store name</strong><p>'+JSON.parse(localStorage.getItem('pargoPoint')).storeName +'</p><br><strong>Store location</strong><p>'+JSON.parse(localStorage.getItem('pargoPoint')).address1+', '+JSON.parse(localStorage.getItem('pargoPoint')).city+', '+JSON.parse(localStorage.getItem('pargoPoint')).postalcode+', '+JSON.parse(localStorage.getItem('pargoPoint')).province+', '+JSON.parse(localStorage.getItem('pargoPoint')).suburb+'</p></div>');


            $('.pargo-btn').text(btnText);
            $(this).css("background-color",btnHoverColor);
            $(this).css("color",btnTextColor);

            $(".pargo-btn").mouseover(function() {
                $(this).css("background-color",btnHoverColor);
                $(this).css("color",btnTextHoverColor);
            }).mouseout(function() {
                $(this).css("background-color",btnColor);
                $(this).css("color",btnTextColor);
            });
        }
        else{

            var btnText = 'Select a Pargo Point';
            var btnTextColor = '#ffffff';
            var btnTextHoverColor = '#000000';
            var btnColor = '#3475B7';
            var btnHoverColor = '#3475B7';

            $('.form-shipping-address').hide();
            $('.pargo-store-info').remove();
            $('.pargo-btn').show();
            $('#pargo-point').hide();


            $('.pargo-btn').text(btnText);
            $(this).css("background-color",btnHoverColor);
            $(this).css("color",btnTextColor);

            $(".pargo-btn").mouseover(function() {
                $(this).css("background-color",btnHoverColor);
                $(this).css("color",btnTextHoverColor);
            }).mouseout(function() {
                $(this).css("background-color",btnColor);
                $(this).css("color",btnTextColor);
            });

        }

    }

    /*    var customerLoggedIn = false;
        if(JSON.parse(localStorage.getItem('mage-cache-storage'))['customer'].firstname === undefined){
            customerLoggedIn = false;
        }
        else {
            customerLoggedIn = true;
        }


    $(window).load(function () {



        if(localStorage.getItem('pargopoint') === null) {

            (function (win) {
                'use strict';

                var listeners = [],
                    doc = win.document,
                    MutationObserver = win.MutationObserver || win.WebKitMutationObserver,
                    observer;

                function ready(selector, fn) {
                    // Store the selector and callback to be monitored
                    listeners.push({
                        selector: selector,
                        fn: fn
                    });
                    if (!observer) {
                        // Watch for changes in the document
                        observer = new MutationObserver(check);
                        observer.observe(doc.documentElement, {
                            childList: true,
                            subtree: true
                        });
                    }
                    // Check if the element is currently in the DOM
                    check();
                }

                function check() {
                    // Check the DOM for elements matching a stored selector
                    for (var i = 0, len = listeners.length, listener, elements; i < len; i++) {
                        listener = listeners[i];
                        // Query for elements matching the specified selector
                        elements = doc.querySelectorAll(listener.selector);
                        for (var j = 0, jLen = elements.length, element; j < jLen; j++) {
                            element = elements[j];
                            // Make sure the callback isn't invoked with the
                            // same element more than once
                            if (!element.ready) {
                                element.ready = true;
                                // Invoke the callback with the element
                                listener.fn.call(element, element);
                            }
                        }
                    }
                }

                // Expose `ready`
                win.ready = ready;

            })(this);

            ready('.pargo-btn', function (element) {

              if ($('.radio:checked').val() == 'pargo_customshipping_pargo_customshipping' && localStorage.getItem('pargopoints') === null ){
                    $('.continue').prop('disabled', true);
                    $("#pargo-point").css("display", "block");
                    $('.pargo-proceed-msg').remove();
                    $( "#pargo-point" ).append( "<p class='pargo-proceed-msg'>Please select a Pargo Point to proceed.</p>" );
                    if(customerLoggedIn === false) {
                        $('.form-shipping-address').hide();
                    }

                }

                else if ($('.radio:checked').val() == 'pargo_customshipping_pargo_customshipping'  && localStorage.getItem('pargopoints') !== null ){

              }

            });

        }


        if(localStorage.getItem('pargopoint')) {

            (function(win) {
                'use strict';

                var listeners = [],
                    doc = win.document,
                    MutationObserver = win.MutationObserver || win.WebKitMutationObserver,
                    observer;

                function ready(selector, fn) {
                    // Store the selector and callback to be monitored
                    listeners.push({
                        selector: selector,
                        fn: fn
                    });
                    if (!observer) {
                        // Watch for changes in the document
                        observer = new MutationObserver(check);
                        observer.observe(doc.documentElement, {
                            childList: true,
                            subtree: true
                        });
                    }
                    // Check if the element is currently in the DOM
                    check();
                }

                function check() {
                    // Check the DOM for elements matching a stored selector
                    for (var i = 0, len = listeners.length, listener, elements; i < len; i++) {
                        listener = listeners[i];
                        // Query for elements matching the specified selector
                        elements = doc.querySelectorAll(listener.selector);
                        for (var j = 0, jLen = elements.length, element; j < jLen; j++) {
                            element = elements[j];
                            // Make sure the callback isn't invoked with the
                            // same element more than once
                            if (!element.ready) {
                                element.ready = true;
                                // Invoke the callback with the element
                                listener.fn.call(element, element);
                            }
                        }
                    }
                }

                // Expose `ready`
                win.ready = ready;

            })(this);




                    $( ".opc-progress-bar-item" ).click(function() {
                        alert(true);
                        if(customerLoggedIn === true) {
                            $('.checkout-shipping-address').hide();
                        }
                        else {
                            $('.form-shipping-address').hide();

                        }
                    });


            $('.payment-method-billing-address').css('display','none');


                ready('billing-address-same-as-shipping-block', function(element) {

                if(customerLoggedIn === false) {
                    $('input[name="billing-address-same-as-shipping"]').trigger('click');
                }
                else {

                    $('.action-update').trigger('click');
                    $('.payment-method-billing-address').hide();


                }

                $( ".action-edit" ).click(function() {
                    if(customerLoggedIn === true) {
                        $('.checkout-shipping-address').hide();
                    }
                    else {
                        $('.form-shipping-address').hide();

                    }
                });
            });

                ready('.pargo-btn', function(element) {


                if($('.radio:checked').val() == 'pargo_customshipping_pargo_customshipping' && JSON.parse(localStorage.getItem('mage-cache-storage'))['checkout-data'].selectedShippingRate === 'pargo_customshipping_pargo_customshipping' && localStorage.getItem('pargopoint') !== null){

                    if(customerLoggedIn === true) {
                        $('.checkout-shipping-address').hide();
                    }
                    $('.close').trigger('click');
                    $('.continue').prop('disabled', false);
                    $(".pargo-btn").removeClass("hide-pargo");
                    $(".pargo-btn").trigger("click");
                    $(".form-shipping-address").css("display", "none");
                    $(".pargo-btn").text('Change Pargo Point');
                    $("#pargo-point").css("display", "block");
                    $('.pargo-proceed-msg').remove();
                    $(".pargo-btn").css({"background-color": "#fff200", "border": "1px solid #fff200"});

                    $(".pargo-btn").mouseover(function () {
                        $(this).css({"background-color": "#1b75bc", "border": "1px solid #1b75bc"});
                    }).mouseout(function () {
                        $(this).css({"background-color": "#fff200", "border": "1px solid #fff200"});
                    });

                    $("#store-name").text(JSON.parse(localStorage.getItem('mage-cache-storage'))['checkout-data'].shippingAddressFromData.company);
                    $("#store-address").text(JSON.parse(localStorage.getItem('mage-cache-storage'))['checkout-data'].shippingAddressFromData.street[0]);

                }
                else if ($('.radio:checked').val() == 'pargo_customshipping_pargo_customshipping' && localStorage.getItem('pargopoint') === null) {
                    $('.continue').prop('disabled', true);
                    $("#pargo-point").css("display", "block");
                    $("#pargo-point").text('Please select a Pargo point to proceed.');
                    $('.pargo-proceed-msg').remove();
                    $(".pargo-btn").show();
                    $(".form-shipping-address").css("display", "block");
                }
               else {

                    localStorage.removeItem('pargopoint');
                    $('.checkout-shipping-address').show();
                    $('.continue').prop('disabled', false);
                    $("#pargo-point").css("display", "none");
                    $('.pargo-proceed-msg').remove();
                    $(".pargo-btn").addClass("hide-pargo");
                    $('input[name=firstname]').val('').change();
                    $('input[name=lastname]').val('').change();
                    $('input[name=company]').val('').change();
                    $('input[name="street[0]"]').val('').change();
                    $('input[name="street[1]"]').val('').change();
                    $('input[name=city]').val('').change();
                    $('input[name=region]').val('').change();
                    $('input[name=postcode]').val('').change();
                    $('input[name=telephone]').val('').change();
                    $('select[name=country_id]').val('ZA').change();
                    $(".form-shipping-address").css("display", "block");
                }

            });

        }

        $(document).on('click', '.radio, .pargo-point-modal', function (e) {
            if ($(this).val() === 'pargo_customshipping_pargo_customshipping' &&  localStorage.getItem('pargopoint') === null ) {
                $('.pargo-proceed-msg').remove();
                if(customerLoggedIn === true) {
                    $('.checkout-shipping-address').hide();
                }
                else {
                    $('.form-shipping-address').hide();

                }
                $('.continue').prop('disabled', true);
                $(".pargo-btn").removeClass("hide-pargo");
                $(".pargo-btn").text('Select Pargo pickup Point');
                $("#pargo-point").css("display", "block");
                $('.pargo-proceed-msg').remove();
                $( "#pargo-point" ).append( "<p class='pargo-proceed-msg'>Please select a Pargo Point to proceed.</p>" );


            } else {
                localStorage.removeItem('pargopoint');
                $('.continue').prop('disabled', false);
                $("#pargo-point").css("display", "none");
                $('.pargo-proceed-msg').remove();
                $(".pargo-btn").addClass("hide-pargo");
                $('input[name=firstname]').val('').change();
                $('input[name=lastname]').val('').change();
                $('input[name=company]').val('').change();
                $('input[name="street[0]"]').val('').change();
                $('input[name="street[1]"]').val('').change();
                $('input[name=city]').val('').change();
                $('input[name=region]').val('').change();
                $('input[name=postcode]').val('').change();
                $('input[name=telephone]').val('').change();
                $('select[name=country_id]').val('ZA').change();
                $('.checkout-shipping-address').show();
                $('.form-shipping-address').show();
            }
        });

        if (window.addEventListener) {
            window.addEventListener("message", selectPargoPoint, false);
        } else {
            window.attachEvent("onmessage", selectPargoPoint);
            $(".form-shipping-address").css("display", "none");
        }

        function selectPargoPoint(item)
        {

            localStorage.setItem('pargopoint', JSON.stringify(item.data));
            $(".close").trigger("click");

            $('input[name=firstname]').val(item.data["storeName"]).change();
            $('input[name=lastname]').val('PARGO POINT-' + item.data["pargoPointCode"]).change();
            $('input[name=company]').val(item.data["storeName"]).change();
            $('input[name="street[0]"]').val(item.data["address1"]).change();
            $('input[name="street[1]"]').val(item.data["address2"]).change();
            $('input[name=city]').val(item.data["city"]).change();
            $('input[name=region]').val(item.data["province"]).change();
            $('input[name=postcode]').val(item.data["postalcode"]).change();
            $('input[name=telephone]').val(item.data["phoneNumber"]).change();
            $('select[name=country_id]').val('ZA').change();
            if(customerLoggedIn === true) {
                $('.checkout-shipping-address').hide();
            }
            else {
                $('.form-shipping-address').hide();

            }
            $("#pargo-location").text(item.data["storeName"]);
            setTimeout(function () {
                $("#store-name").text(item.data["storeName"]);
                $("#store-address").text(item.data["address1"]);
                $(".pargo-btn").css({"background-color": "#fff200", "border": "1px solid #fff200"});

                $(".pargo-btn").mouseover(function () {
                    $(this).css({"background-color": "#1b75bc", "border": "1px solid #1b75bc"});
                }).mouseout(function () {
                    $(this).css({"background-color": "#fff200", "border": "1px solid #fff200"});
                });

                $(".pargo-btn").text('Change Pargo Point');
                $("#pargo-point").css("display", "block");
                $('.pargo-proceed-msg').remove();
                $('.continue').prop('disabled', false);
            }, 5000);

            var mageData = [];
            var ExistingMageData = localStorage.getItem('mage-cache-storage');
            if(ExistingMageData != null){
                mageData = JSON.parse(ExistingMageData);
            }

            mageData['checkout-data'].selectedShippingRate = 'pargo_customshipping_pargo_customshipping';

            localStorage.setItem('mage-cache-storage', JSON.stringify(mageData));



        }

    });
*/

});

