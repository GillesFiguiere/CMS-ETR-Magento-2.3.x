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
 * @version   1.0.8-meqp
 * @author    E-Transactions <support@e-transactions.fr>
 * @copyright 2012-2017 E-Transactions
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.e-transactions.fr/
 */

namespace ETransactions\Epayment\Model\Payment;

class Paybuttons extends AbstractPayment
{
    const CODE = 'etep_paybuttons';
    const XML_PATH = 'payment/etep_paybuttons/cctypes';

    protected $_code = self::CODE;
    protected $_3dsAllowed = true;
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
}
