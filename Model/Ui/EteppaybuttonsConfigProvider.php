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

namespace CreditAgricole\Etransactions\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
// use Magento\Framework\App\Config\ScopeConfigInterface;
// use Magento\Framework\View\Asset\Source;
use \Magento\Framework\ObjectManagerInterface;
use CreditAgricole\Etransactions\Gateway\Http\Client\ClientMock;
use CreditAgricole\Etransactions\Model\Ui\EteppaybuttonsConfig;

/**
 * Class ConfigProvider
 */
final class EteppaybuttonsConfigProvider implements ConfigProviderInterface
{
    const CODE = 'etep_paybuttons';

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'cards' => $this->getCards()
                ]
            ]
        ];
    }

    public function getCards()
    {
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $eteppaybuttonsConfig = $object_manager->get('CreditAgricole\Etransactions\Model\Ui\EteppaybuttonsConfig');
        $assetSource = $object_manager->get('Magento\Framework\View\Asset\Source');
        $assetRepository = $object_manager->get('Magento\Framework\View\Asset\Repository');

        $cards = [];
        $types = $eteppaybuttonsConfig->getCards();
        if (!is_array($types)) {
            $types = explode(',', $types);
        }
        foreach ($types as $code) {
            $asset = $assetRepository->createAsset('CreditAgricole_Etransactions::images/' . strtolower($code) . '.45.png');
            $placeholder = $assetSource->findRelativeSourceFilePath($asset);
            if ($placeholder) {
                list($width, $height) = getimagesize($asset->getSourceFile());
                $cards[] = [
                    'value' => $code,
                    'url' => $asset->getUrl(),
                    'title' => $code,
                    'width' => $width,
                    'height' => $height
                ];
            }
        }
        return $cards;
    }
}
