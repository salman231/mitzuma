/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
    define(function (require){
        'use strict';

        var renderer = require('Magento_Ui/js/lib/knockout/template/renderer');
        renderer.addAttribute('repeat', renderer.handlers.wrapAttribute);

        renderer.addAttribute('outerfasteach', {
            binding: 'fastForEach',
            handler: renderer.handlers.wrapAttribute
        });

        renderer
            .addNode('repeat')
            .addNode('fastForEach');


        var tmp= {
            i18n: require('Magento_Ui/js/lib/knockout/bindings/i18n'),
            scope: require('Magento_Ui/js/lib/knockout/bindings/scope'),
            mageInit: require('Magento_Ui/js/lib/knockout/bindings/mage-init'),
            keyboard: require('Magento_Ui/js/lib/knockout/bindings/keyboard'),
            range:    require('Magento_Ui/js/lib/knockout/bindings/range'),
            afterRender: require('Magento_Ui/js/lib/knockout/bindings/after-render'),
            autoselect: require('Magento_Ui/js/lib/knockout/bindings/autoselect'),
            outerClick: require('Magento_Ui/js/lib/knockout/bindings/outer_click'),
            fadeVisible: require('Magento_Ui/js/lib/knockout/bindings/fadeVisible'),
            collapsible: require('Magento_Ui/js/lib/knockout/bindings/collapsible'),
            staticChecked: require('Magento_Ui/js/lib/knockout/bindings/staticChecked'),
            simpleChecked: require('Magento_Ui/js/lib/knockout/bindings/simple-checked'),
            bindHtml: require('Magento_Ui/js/lib/knockout/bindings/bind-html'),
            tooltip: require('Magento_Ui/js/lib/knockout/bindings/tooltip'),
            repeat: require('knockoutjs/knockout-repeat'),
            fastForEach: require('knockoutjs/knockout-fast-foreach'),
        };

        if(jQuery('body').hasClass('checkout-index-index')){
            tmp['optgroup'] = require('Magento_Ui/js/lib/knockout/bindings/optgroup');
        }

    });
