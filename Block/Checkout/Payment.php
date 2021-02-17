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
 * @version   1.0.7-psr
 * @author    E-Transactions <support@e-transactions.fr>
 * @copyright 2012-2017 E-Transactions
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.e-transactions.fr/
 */

namespace ETransactions\Epayment\Block\Checkout;

class Payment
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('etep/checkout-payment.phtml');
    }

    protected function _prepareLayout()
    {
        $head = $this->getLayout()->getBlock('head');
        if (!empty($head)) {
            $head->addCss('css/etep/styles.css');
        }
        return parent::_prepareLayout();
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

    public function getCards()
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

    public function getMethodLabelAfterHtml()
    {
        $cards = $this->getCreditCards();
        $html = [];
        foreach ($cards as $card) {
            $url = $this->htmlEscape($this->getSkinUrl($card['image']));
            $alt = $this->htmlEscape($card['label']);
            $html[] = '<img class="etep-payment-logo" src="'.$url.'" alt="'.$alt.'"/>';
        }
        $html = '<span class="etep-payment-label">'.implode('&nbsp;', $html).'</span>';
        return $html;
    }
}
