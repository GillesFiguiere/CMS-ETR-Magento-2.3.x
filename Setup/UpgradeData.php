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
 * @version   1.0.7-psr
 * @author    E-Transactions <support@e-transactions.fr>
 * @copyright 2012-2017 E-Transactions
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.e-transactions.fr/
 */

namespace creditagricole\etransactions\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     *
     * @param WriterInterface $writerInterface
     */
    public function __construct(WriterInterface $writerInterface) {
        $this->writerInterface = $writerInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Merge CB/VISA/MC/ECB into one method
        if (!empty($context->getVersion()) && version_compare($context->getVersion(), '2.0.2', '<=')) {
            $this->writerInterface->save('payment/etep_cb/cctypes', 'CB', 'default', 0);
            $this->writerInterface->save('payment/etep_threetime/cctypes', 'CB', 'default', 0);
        }

        $setup->endSetup();
    }
}
