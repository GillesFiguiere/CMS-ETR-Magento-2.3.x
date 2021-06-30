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
 * @version   1.0.8-meqp
 * @author    E-Transactions <support@e-transactions.fr>
 * @copyright 2012-2021 E-Transactions
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.e-transactions.fr/
 */

namespace CreditAgricole\Etransactions\Model;

class Context
{
    private $_order;
    private $_objectManager;
    private $_helper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \CreditAgricole\Etransactions\Helper\Data $helper
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
    }

    public static function generateToken(\Magento\Sales\Model\Order $order)
    {
        $reference = [];
        $reference[] = $order->getRealOrderId();
        $reference[] = $order->getCustomerName();
        $reference = implode(' - ', $reference);
        return $reference;
    }

    public function getOrder()
    {
        return $this->_order;
    }


    /**
     * Reference = order id and customer name
     * The data integrity check is provided by the customer name
     */
    public function getToken()
    {
        return self::generateToken($this->getOrder());
    }

    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
    }

    public function setToken($reference)
    {
        $parts = explode(' - ', $reference, 2);
        if (count($parts) < 2) {
            $message = 'Invalid decrypted reference "%s"';
            throw new \LogicException($this->_helper->__($message, $reference));
        }

        // Retrieves order
        $order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($parts[0]);
        if (empty($order)) {
            $message = 'Not existing order id from decrypted reference "%s"';
            throw new \LogicException($this->_helper->__($message, $reference));
        }
        if (is_null($order->getId())) {
            $message = 'Not existing order id from decrypted reference "%s"';
            throw new \LogicException($this->_helper->__($message, $reference));
        }
        if ($order->getCustomerName() != $parts[1]) {
            $message = 'Consistency error on descrypted reference "%s"';
            throw new \LogicException($this->_helper->__($message, $reference));
        }

        $this->_order = $order;
    }
}
