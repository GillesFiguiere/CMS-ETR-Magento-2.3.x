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
 * @version   1.0.0
 * @author    LicenseAuthor
 * @copyright LicenseCopyright
 * @license   LicenseLicense
 * @link      LicenseLink
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'etep_cb',
                component: 'CreditAgricole_Etransactions/js/view/payment/method-renderer/etep_multi-method'
            },
            {
                type: 'etep_threetime',
                component: 'CreditAgricole_Etransactions/js/view/payment/method-renderer/etep_multi-method'
            },
            {
                type: 'etep_paypal',
                component: 'CreditAgricole_Etransactions/js/view/payment/method-renderer/etep_simple-method'
            },
            {
                type: 'etep_private',
                component: 'CreditAgricole_Etransactions/js/view/payment/method-renderer/etep_multi-method'
            },
            {
                type: 'etep_prepaid',
                component: 'CreditAgricole_Etransactions/js/view/payment/method-renderer/etep_multi-method'
            },
            {
                type: 'etep_financial',
                component: 'CreditAgricole_Etransactions/js/view/payment/method-renderer/etep_multi-method'
            },
            {
                type: 'etep_bcmc',
                component: 'CreditAgricole_Etransactions/js/view/payment/method-renderer/etep_simple-method'
            },
            {
                type: 'etep_paybuttons',
                component: 'CreditAgricole_Etransactions/js/view/payment/method-renderer/etep_multi-method'
            },
            {
                type: 'etep_maestro',
                component: 'CreditAgricole_Etransactions/js/view/payment/method-renderer/etep_simple-method'
            }
        );

        // Add view logic here if needed

        return Component.extend({});
    }
);
