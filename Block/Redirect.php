<?php
/**
 * CreditAgricole etransactions module for Magento
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
 * @version   1.0.7-psr
 * @author    E-Transactions <support@e-transactions.fr>
 * @copyright 2012-2017 E-Transactions
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.e-transactions.fr/
 */

namespace ETransactions\etransactions\Block;

class Redirect extends \Magento\Framework\View\Element\Template
{
    protected $_objectManager;
    protected $_helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \ETransactions\etransactions\Helper\Data $helper
    ) {
        parent::__construct($context, $data);

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_helper = $helper;
    }

    public function getFormFields()
    {
        $registry = $this->_objectManager->get('Magento\Framework\Registry');
        $current_order_id = $this->_objectManager->get('Magento\Checkout\Model\Session')->getCurrentEtepOrderId();
        $order = $registry->registry('etep/order_'.$current_order_id);
        $payment = $order->getPayment()->getMethodInstance();
        $cntr = $this->_objectManager->get('ETransactions\etransactions\Model\Etransactions');
        return $cntr->buildSystemParams($order, $payment);
    }

    public function getInputType()
    {
        $config = $this->_objectManager->get('ETransactions\etransactions\Model\Config');
        if ($config->isDebug()) {
            return 'text';
        }
        return 'hidden';
    }

    public function getKwixoUrl()
    {
        $creditagricole = $this->_objectManager->get('ETransactions\etransactions\Model\Etransactions');
        $urls = $creditagricole->getConfig()->getKwixoUrls();
        return $creditagricole->checkUrls($urls);
    }

    public function getMobileUrl()
    {
        $creditagricole = $this->_objectManager->get('ETransactions\etransactions\Model\Etransactions');
        $urls = $creditagricole->getConfig()->getMobileUrls();
        return $creditagricole->checkUrls($urls);
    }

    public function getSystemUrl()
    {
        $creditagricole = $this->_objectManager->get('ETransactions\etransactions\Model\Etransactions');
        $urls = $creditagricole->getConfig()->getSystemUrls();
        return $creditagricole->checkUrls($urls);
    }

    public function getResponsiveUrl()
    {
        $creditagricole = $this->_objectManager->get('ETransactions\etransactions\Model\Etransactions');
        $urls = $creditagricole->getConfig()->getResponsiveUrls();
        return $creditagricole->checkUrls($urls);
    }
}
