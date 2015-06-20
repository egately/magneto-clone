/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Ui/js/model/errorlist',
        'Magento_Customer/js/model/customer'
    ],
    function (quote, urlBuilder, storage, errorList, customer) {
        'use strict';

        return function () {
            var serviceUrl,
                payload,
                paymentData = quote.paymentMethod();

            /**
             * Checkout for guest and registered customer.
             */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/set-payment-information', {
                    quoteId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/set-payment-information', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            }
            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).done(
                function () {
                    //do nothing
                }
            ).fail(
                function (response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error);
                }
            );
        };
    }
);
