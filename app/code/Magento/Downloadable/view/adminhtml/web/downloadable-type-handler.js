/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true expr:true*/
define([
    'jquery'
], function($) {
    "use strict";

    return {
        $checkbox: $('[data-action=change-type-product-downloadable]'),
        $items: $('#product_info_tabs_downloadable_items'),
        $tab: null,
        isDownloadable: false,
        show: function () {
            this.$checkbox.prop('checked', true);
            this.$items.show();
        },
        hide: function () {
            this.$checkbox.prop('checked', false);
            this.$items.hide();
        },
        'Magento_Downloadable/downloadable-type-handler': function(data) {
            this.$tab = $('[data-tab='+data.tabId+']');
            this.isDownloadable = data.isDownloadable;
            this.bindAll();
        },
        bindAll: function () {
            this.$checkbox.on('change', function (event) {
                $(document).trigger('setTypeProduct', $(event.target).prop('checked') ? 'downloadable' : null);
            }.bind(this));

            $(document).on('changeTypeProduct', function (event, controllers) {
                if (controllers.type.current == 'downloadable') {
                    controllers.weight.change(false);
                    controllers.weight.$weightSwitcher.one('change', function () {
                        $(document).trigger('setTypeProduct', null);
                    });
                    controllers.downloadable.show();
                    controllers.configurable.hide();
                } else {
                    controllers.downloadable.hide();
                    controllers.configurable.show();
                }
            });
        }
    };
});
