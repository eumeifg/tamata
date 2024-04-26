<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\Sales\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class UpgradeDataPatch implements
    DataPatchInterface,
    PatchRevertableInterface,
    PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    private $serializer;

    private $resourceConfig;

    /**
    * Xpath to dynamic field value
    */
    const XPATH_DYNAMIC_FIELD = 'vendor_sales/cancel_order/order_cancel_reasons_customer';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SerializerInterface $serializer,
        Config $resourceConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->serializer = $serializer;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        /* Added Order Cancellations Reason */
        $defaultValues = [
            ['order_cancel_reasons' => 'Cheaper alternative available'],
            ['order_cancel_reasons' => 'Product is not required anymore'],
            ['order_cancel_reasons' => 'Bad review from friends'],
            ['order_cancel_reasons' => 'Product is not same as expected'],
        ];
        $serializedValue = $this->serializer->serialize($defaultValues);
        $this->resourceConfig->saveConfig(
            self::XPATH_DYNAMIC_FIELD,
            $serializedValue,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    public function revert()
    {
        $this->moduleDataSetup->startSetup();

        $this->resourceConfig->deleteConfig(
            self::XPATH_DYNAMIC_FIELD,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->moduleDataSetup->endSetup();
    }

    public static function getVersion()
    {
        return '1.0.0';
    }
}
