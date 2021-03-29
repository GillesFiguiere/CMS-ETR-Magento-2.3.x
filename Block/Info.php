<?php
/**
 * E-Transactions Epayment module for Magento
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
 * @version   1.0.10
 * @author    E-Transactions <support@e-transactions.fr>
 * @copyright 2012-2017 E-Transactions
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.e-transactions.fr/
 */

namespace ETransactions\Epayment\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use ETransactions\Epayment\Gateway\Response\FraudHandler;

class Info extends ConfigurableInfo
{
    protected $_object_manager;

    /**
     * Returns label
     *
     * @param  string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Returns value view
     *
     * @param  string $field
     * @param  string $value
     * @return string | Phrase
     */
    protected function getValueView($field, $value)
    {
        switch ($field) {
            case FraudHandler::FRAUD_MSG_LIST:
                return implode('; ', $value);
        }
        return parent::getValueView($field, $value);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('etep/info/default.phtml');
        $this->_object_manager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getCreditCards()
    {
        $result = [];
        $cards = $this->getMethod()->getCards();
        $selected = explode(',', $this->getMethod()->getConfigData('cctypes'));
        foreach ($cards as $code => $card) {
            if (in_array($code, $selected)) {
                $result[$code] = $card;
            }
        }
        return $result;
    }

    public function getEtransactionsData()
    {
        return unserialize($this->getInfo()->getEtepAuthorization());
    }

    public function getObjectManager()
    {
        return $this->_object_manager;
    }

    public function getEtransactionsConfig()
    {
        return $this->_object_manager->get('ETransactions\Epayment\Model\Config');
    }

    public function getCardImageUrl()
    {
        $data = $this->getEtransactionsData();
        $cards = $this->getCreditCards();
        if (!isset($data['cardType'])) {
            return null;
        }
        return $this->getViewFileUrl(
            'ETransactions_Epayment::' . 'images/' .strtolower($data['cardType']).'.45.png',
            ['area'  => 'frontend', 'theme' => 'Magento/luma']
        );
    }

    public function getCardImageLabel()
    {
        $data = $this->getEtransactionsData();
        $cards = $this->getCreditCards();
        if (!isset($data['cardType'])) {
            return null;
        }
        if (!isset($cards[$data['cardType']])) {
            return null;
        }
        $card = $cards[$data['cardType']];
        return $card['label'];
    }

    public function isAuthorized()
    {
        $info = $this->getInfo();
        $auth = $info->getEtepAuthorization();
        return !empty($auth);
    }

    public function canCapture()
    {
        $info = $this->getInfo();
        $capture = $info->getEtepCapture();
        $config = $this->getEtransactionsConfig();
        if ($config->getSubscription() == \ETransactions\Epayment\Model\Config::SUBSCRIPTION_OFFER2 ||
            $config->getSubscription() == \ETransactions\Epayment\Model\Config::SUBSCRIPTION_OFFER3) {
            if ($info->getEtepAction() == \ETransactions\Epayment\Model\Payment\AbstractPayment::ETRANSACTION_MANUAL) {
                $order = $info->getOrder();
                return empty($capture) && $order->canInvoice();
            }
        }
        return false;
    }

    public function canRefund()
    {
        $info = $this->getInfo();
        $capture = $info->getEtepCapture();
        $config = $this->getEtransactionsConfig();
        if ($config->getSubscription() == \ETransactions\Epayment\Model\Config::SUBSCRIPTION_OFFER2 ||
            $config->getSubscription() == \ETransactions\Epayment\Model\Config::SUBSCRIPTION_OFFER3) {
            return !empty($capture);
        }
        return false;
    }

    public function getDebitTypeLabel()
    {
        $info = $this->getInfo();
        $action = $info->getEtepAction();
        if (null === $action || ($action == 'three-time')) {
            return null;
        }

        $action = $info->getEtepAction();
        $action_model = new \ETransactions\Epayment\Model\Admin\Payment\Action();
        $actions = $action_model->toOptionArray();
        $result = '';
        foreach ($actions as $act) {
            if ($act['value'] == $action) {
                $result = $act['label'];
            }
        }
        if (($info->getEtepAction() == \ETransactions\Epayment\Model\Payment\AbstractPayment::ETRANSACTION_DEFERRED) &&
            (null !== $info->getEtepDelay())) {
            $delays = new \ETransactions\Epayment\Model\Admin\Payment\Delays();
            $delays = $delays->toOptionArray();
            $result .= ' (' . $delays[$info->getEtepDelay()]['label'] . ')';
        }
        return $result;
    }

    public function getThreeTimeLabels()
    {
        $info = $this->getInfo();
        $action = $info->getEtepAction();
        if (null === $action || ($action != 'three-time')) {
            return null;
        }
        $result = [
           'first' => __('Not achieved'),
           'second' => __('Not achieved'),
           'third' => __('Not achieved'),
        ];
        $data = $info->getEtepFirstPayment();
        if (!empty($data)) {
            $data = unserialize($data);
            $date = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{4})$/', '$1/$2/$3', $data['date']);
            $result['first'] = sprintf('%s (%s)', $data['amount'] / 100.0, $date);
        }
        $data = $info->getEtepSecondPayment();
        if (!empty($data)) {
            $data = unserialize($data);
            $date = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{4})$/', '$1/$2/$3', $data['date']);
            $result['second'] = sprintf('%s (%s)', $data['amount'] / 100.0, $date);
        }
        $data = $info->getEtepThirdPayment();
        if (!empty($data)) {
            $data = unserialize($data);
            $date = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{4})$/', '$1/$2/$3', $data['date']);
            $result['third'] = sprintf('%s (%s)', $data['amount'] / 100.0, $date);
        }
        return $result;
    }

    public function getPartialCaptureUrl()
    {
        $data = $this->getEtransactionsData();
        $info = $this->getInfo();
        return $this->getUrl(
            'etransactions/partial',
            ['order_id' => $info->getOrder()->getId(), 'transaction' => $data['transaction']]
        );
    }

    public function getCaptureUrl()
    {
        $data = $this->getEtransactionsData();
        $info = $this->getInfo();
        return $this->getUrl(
            'etransactions/capture',
            ['order_id' => $info->getOrder()->getId(), 'transaction' => $data['transaction']]
        );
    }

    public function getRefundUrl()
    {
        $info = $this->getInfo();
        $order = $info->getOrder();
        $invoices = $order->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            if ($invoice->canRefund()) {
                return $this->getUrl(
                    'sales/order_creditmemo/start',
                    ['order_id' => $order->getId(), 'invoice_id' => $invoice->getId()]
                );
            }
        }
        return null;
    }
}
