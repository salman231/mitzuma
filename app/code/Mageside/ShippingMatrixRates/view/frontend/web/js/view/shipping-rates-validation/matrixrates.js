/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../../model/shipping-rates-validator/matrixrates',
        '../../model/shipping-rates-validation-rules/matrixrates'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        matrixratesShippingRatesValidator,
        matrixratesShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('matrixrates', matrixratesShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('matrixrates', matrixratesShippingRatesValidationRules);
        return Component;
    }
);
