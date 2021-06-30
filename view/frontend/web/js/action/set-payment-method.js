/**
 * CreditAgricole Etransactions module for Magento
 *
 * Feel free to contact LicenseCompany at LicenseEmailContact for any
 * question.
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0. If
 * you did not receive a copy of the OSL-3.0 license and are unable
 * to obtain it through the web, please send a note to
 * LicenseEmailContact so we can mail you a copy immediately.
 *
 * @version   1.0.8-meqp
 * @author    LicenseAuthor
 * @copyright LicenseCopyright
 * @license   LicenseLicense
 * @link      LicenseLink
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url',
    ],
    function ($, quote, urlBuilder, storage, errorProcessor, customer, fullScreenLoader, url) {
        'use strict';

        return function (messageContainer) {
            var serviceUrl,
                payload,
                paymentData = quote.paymentMethod();

            var filterTemplateData = function (data) {
                return _.each(data, function (value, key, list) {
                    if (_.isArray(value) || _.isObject(value)) {
                        list[key] = filterTemplateData(value);
                    }

                    if (key === '__disableTmpl') {
                        delete list[key];
                    }
                });
            };

            paymentData = filterTemplateData(paymentData);

            /**
             * Checkout for guest and registered customer.
             */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl(
                    '/guest-carts/:cartId/selected-payment-method',
                    {
                        cartId: quote.getQuoteId()
                    }
                );
                payload = {
                    cartId: quote.getQuoteId(),
                    method: paymentData,
                    billingAddress: quote.billingAddress()
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/selected-payment-method', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    method: paymentData,
                    billingAddress: quote.billingAddress()
                };
            }

            fullScreenLoader.startLoader();

            return storage.put(
                serviceUrl,
                JSON.stringify(payload)
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                    fullScreenLoader.stopLoader();
                }
            );
        };
    }
);
