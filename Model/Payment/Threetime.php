<?php
/**
 * ETransactions Etransactions module for Magento
 *
 * Feel free to contact E-Transactions at support@e-transactions.fr for any
 * question.
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0. If
 * you did not receive a copy of the OSL-3.0 license and are unable
 * to obtain it through the web, please send a note to
 * support@e-transactions.fr so we can mail you a copy immediately.
 *
 * @version   1.0.8-meqp
 * @author    E-Transactions <support@e-transactions.fr>
 * @copyright 2012-2017 E-Transactions
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.e-transactions.fr/
 */

namespace CreditAgricole\Etransactions\Model\Payment;

use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\Order\Payment\Transaction;
use \Magento\Framework\Validator\Exception;

class Threetime extends AbstractPayment
{
    const CODE = 'etep_threetime';
    const XML_PATH = 'payment/etep_threetime/cctypes';

    protected $_code = self::CODE;
    protected $_hasCctypes = true;
    protected $_allowManualDebit = true;
    protected $_allowDeferredDebit = true;
    protected $_allowRefund = true;

    public function getReceipentEmail()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue(self::XML_PATH, $storeScope);
    }

    public function toOptionArray()
    {
        $result = [];
        $configPath = $this->getConfigPath();
        $cards = $this->_getConfigValue($configPath);
        if (!empty($cards)) {
            foreach ($cards as $code => $card) {
                $result[] = [
                    'label' => __($card['label']),
                    'value' => $code,
                ];
            }
        } else {
            $result[] = [
                'label' => __('CB'),
                'value' => 'CB',
            ];
            $result[] = [
                'label' => __('Visa'),
                'value' => 'VISA',
            ];
            $result[] = [
                'label' => __('Mastercard'),
                'value' => 'EUROCARD_MASTERCARD',
            ];
            $result[] = [
                'label' => __('E-Carte Bleue'),
                'value' => 'E_CARD',
            ];
        }
        return $result;
    }

    public function onIPNSuccess(Order $order, array $data)
    {
        $this->logDebug(sprintf('Order %s: Threetime IPN', $order->getIncrementId()));

        $this->logDebug(sprintf('onIPNSuccess :', $order->getIncrementId()));

        $payment = $order->getPayment();

        // Message

        // Create transaction
        $type = Transaction::TYPE_CAPTURE;
        $txn = $this->_addCreditAgricoleTransaction(
            $order,
            $type,
            $data,
            true,
            [
            self::CALL_NUMBER => $data['call'],
            self::TRANSACTION_NUMBER => $data['transaction'],
            ]
        );

        if (is_null($payment->getEtepFirstPayment())) {
            $this->logDebug(sprintf('Order %s: First payment', $order->getIncrementId()));

            // Message
            $message = 'Payment was authorized and captured by ETransactions.';

            // Status
            $status = $this->getConfigPaidStatus();
            $state = Order::STATE_PROCESSING;
            $allowedStates = [
                Order::STATE_NEW,
                Order::STATE_PENDING_PAYMENT,
                Order::STATE_PROCESSING,
            ];
            $current = $order->getState();
            if (in_array($current, $allowedStates)) {
                $this->logDebug(sprintf('Order %s: Change status to %s', $order->getIncrementId(), $state));
                $order->setState($state, $status, $message);
            } else {
                $order->addStatusHistoryComment($message);
            }

            // Additional informations
            $payment->setEtepFirstPayment(serialize($data));
            $payment->setEtepAuthorization(serialize($data));

            $this->logDebug(sprintf('Order %s: %s', $order->getIncrementId(), $message));

            // Create invoice is needed
            $invoice = $this->_createInvoice($payment, $order, $txn);
        } elseif (is_null($payment->getEtepSecondPayment())) {
            // Message
            $message = 'Second payment was captured by ETransactions.';
            $order->addStatusHistoryComment($message);

            // Additional informations
            $payment->setEtepSecondPayment(serialize($data));
            $this->logDebug(sprintf('Order %s: %s', $order->getIncrementId(), $message));
        } elseif (is_null($payment->getEtepThirdPayment())) {
            // Message
            $message = 'Third payment was captured by ETransactions.';
            $order->addStatusHistoryComment($message);

            // Additional informations
            $payment->setEtepThirdPayment(serialize($data));
            $this->logDebug(sprintf('Order %s: %s', $order->getIncrementId(), $message));
        } else {
            $this->logDebug(sprintf('Order %s: Invalid three-time payment status', $order->getIncrementId()));
            throw new \LogicException('Invalid three-time payment status');
        }
        $data['status'] = $message;

        // Associate data to payment
        $payment->setEtepAction('three-time');

        $payment->save();
        $order->save();
    }
}
