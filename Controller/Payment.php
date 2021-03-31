<?php
/**
 * E-Transactions etransactions module for Magento
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
 * @version   2.0.0
 * @author    E-Transactions <support@e-transactions.fr>
 * @copyright 2012-2017 E-Transactions
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.e-transactions.fr/
 */

namespace creditagricole\etransactions\Controller;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Payment extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $_quoteRepository;
    protected $_logger;
    protected $_checkoutSession;
    protected $_etransactionsConfig;
    protected $_etransactions;
    protected $_registry;

    /**
     * @param \Magento\Framework\App\Action\Context                        $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $loggerInteface,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        \creditagricole\etransactions\Model\Config $etransactionsConfig,
        \creditagricole\etransactions\Model\creditagricole $etransactions,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);

        $this->_logger = $loggerInteface;
        $this->_messageManager = $context->getMessageManager();
        $this->_quoteRepository = $cartRepositoryInterface;
        $this->_checkoutSession = $checkoutSession;
        $this->_etransactionsConfig = $etransactionsConfig;
        $this->_etransactions = $etransactions;
        $this->_registry = $registry;
    }

    public function execute()
    {
    }

    protected function _redirectResponse($order, $success, $checkUrlWarn = false)
    {
        // clear all messages in session
        $this->messageManager->getMessages(true);

        $storeId = $order->getStore()->getId();

        if ($success) {
            $this->_getCheckout()->setLastQuoteId($order->getQuoteId())
                ->setLastSuccessQuoteId($order->getQuoteId())
                ->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderStatus($order->getStatus());

            $this->logDebug('Redirecting to success page.');
            $this->_redirect('checkout/onepage/success');
        } else {
            $this->logDebug('Unsetting order data in session.');
            $this->messageManager->addWarning(__('Checkout and order have been canceled.'));

            $this->logDebug("Restore cart for order #{$order->getId()} to allow re-order quicker.");
            $quote = $this->_quoteRepository->get($order->getQuoteId());
            if ($quote->getId()) {
                $quote->setIsActive(true)->setReservedOrderId(null);
                $this->_quoteRepository->save($quote);

                $this->_getCheckout()->replaceQuote($quote);
            }

            $this->logDebug('Redirecting to cart page.');
            $this->_redirect('checkout/cart', ['_store' => $storeId]);
        }
    }

    protected function _404()
    {
        $this->_registry->register('etep_forward_nocache', true);
        $this->_forward('defaultNoRoute');
    }

    protected function _loadQuoteFromOrder(\Magento\Sales\Model\Order $order)
    {
        $quoteId = $order->getQuoteId();

        // Retrieves quote
        $quote = $this->_quoteRepository->get($quoteId);
        if (empty($quote) || null === $quote->getId()) {
            $message = 'Not existing quote id associated with the order %d';
            throw new \LogicException(__($message, $order->getId()));
        }

        return $quote;
    }

    /**
     * Get checkout session namespace.
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_checkoutSession;
    }

    protected function _getOrderFromParams(array $params)
    {
        // Retrieves order
        $etransactions = $this->getcreditagricole();
        $order = $etransactions->untokenizeOrder($params['reference']);
        if (is_null($order) || is_null($order->getId())) {
            return null;
        }
        return $order;
    }

    public function getConfig()
    {
        return $this->_etransactionsConfig;
    }

    public function getcreditagricole()
    {
        return $this->_etransactions;
    }

    public function getSession()
    {
        return $this->_checkoutSession;
    }

    public function logDebug($message)
    {
        $this->_logger->debug($message);
    }

    public function logWarning($message)
    {
        $this->_logger->warning($message);
    }

    public function logError($message)
    {
        $this->_logger->error($message);
    }

    public function logFatal($message)
    {
        $this->_logger->critical($message);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
