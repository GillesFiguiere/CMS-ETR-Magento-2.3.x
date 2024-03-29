<?php
/**
 * Up2pay e-Transactions Etransactions module for Magento
 *
 * Feel free to contact Credit Agricole at support@e-transactions.fr for any
 * question.
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0. If
 * you did not receive a copy of the OSL-3.0 license and are unable
 * to obtain it through the web, please send a note to
 * support@e-transactions.fr so we can mail you a copy immediately.
 *
 * @version   1.0.7-psr
 * @author    E-Transactions <support@e-transactions.fr>
 * @copyright 2012-2021 E-Transactions
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.e-transactions.fr/
 */

namespace CreditAgricole\Etransactions\Controller\Payment;

class Redirect extends \CreditAgricole\Etransactions\Controller\Payment
{
    public function execute()
    {
        $cookieName = 'lastOrderId';
        $cookieManager = $this->_objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
        $encryptor = $this->_objectManager->get('Magento\Framework\Encryption\Encryptor');
        $registry = $this->_objectManager->get('Magento\Framework\Registry');

        // Retrieves order id
        $session = $this->getSession();
        $orderId = $session->getLastRealOrderId();

        // If none, try previously saved
        $this->logDebug('CreditAgricole - LastRealOrderId from $session: '.$orderId);
        if (is_null($orderId)) {
            $orderId = $session->getCurrentEtepOrderId();
            $this->logDebug('CreditAgricole - CurrentEtepOrderId from $session: '.$orderId);
        }

        //Try with cookies
        $cookieOrderId = $cookieManager->getCookie($cookieName);

        // If none, 404
        if (is_null($orderId)) {
            $this->logDebug('CreditAgricole - $orderId is null => 404');

            $this->logDebug('CreditAgricole - Try to get id from cookies');
            if (!is_null($cookieOrderId)) {
                $this->logDebug('CreditAgricole - Retrieve id from cookies : ' . $cookieOrderId);
                $order = $this->_objectManager->get('Magento\Sales\Model\Order')->load($cookieOrderId);
                if (isset($_COOKIE[$cookieName])) {
                    unset($_COOKIE[$cookieName]);
                }
            } else {
                return $this->_404();
            }
        } else {
            $order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
        }

        // Load order
        $order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
        if (is_null($order) || is_null($order->getId())) {
            $session->unsCurrentEtepOrderId();
            return $this->_404();
        }

        // Check order status
        $state = $order->getState();
        if ($state != \Magento\Sales\Model\Order::STATE_NEW) {
            $session->unsCurrentEtepOrderId();
            return $this->_404();
        }

        // Save id
        $session->setCurrentEtepOrderId($orderId);

        // Keep track of order for security check
        $orders = $session->getEtepOrders();
        if (is_null($orders)) {
            $orders = [];
        }

        $orders[$encryptor->encrypt($orderId)] = true;
        $session->setEtepOrders($orders);

        // Payment method
        $order->getPayment()->getMethodInstance()->onPaymentRedirect($order);

        // Render form
        $registry->register('etep/order_' . $orderId, $order);

        $page = $this->resultPageFactory->create();

        // check that there is products in cart
        if ($order->getTotalDue() == 0) {
            $this->logDebug('CreditAgricole - Payment attempt with no amount : ' . $order->getId());
            return $this->_404();
        }

        // check that order is not processed yet
        if (!$this->_getCheckout()->getLastSuccessQuoteId()) {
            $this->logDebug('CreditAgricole - Payment attempt with a quote already processed : ' . $order->getId());
            return $this->_404();
        }

        // add history comment and save it
        $order->addStatusHistoryComment(__('CreditAgricole - Client sent to Up2pay e-Transactions payment page.'), false)
            ->setIsCustomerNotified(false)
            ->save();

        // clear quote data
        $this->_getCheckout()->unsLastQuoteId()
            ->unsLastSuccessQuoteId()
            ->clearHelperData();

        return $page;
    }
}
