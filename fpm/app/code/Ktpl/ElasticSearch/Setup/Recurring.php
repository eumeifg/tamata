<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Ktpl\ElasticSearch\Service\CompatibilityService;

/**
 * Class Recurring
 *
 * @package Ktpl\ElasticSearch\Setup
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (CompatibilityService::is22()) {
            $this->upgradeSerializeToJson($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Magento\Framework\DB\FieldDataConversionException
     */
    private function upgradeSerializeToJson(SchemaSetupInterface $setup)
    {
        $om = CompatibilityService::getObjectManager();

        /** @var \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory */
        $fieldDataConverterFactory = $om->create('Magento\Framework\DB\FieldDataConverterFactory');

        /** @var \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory */
        $queryModifierFactory = $om->create('Magento\Framework\DB\Select\QueryModifierFactory');

        $fieldDataConverter = $fieldDataConverterFactory->create('Magento\Framework\DB\DataConverter\SerializedToJson');
        $queryModifier = $queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'path' => [
                        'searchautocomplete/general/index',
                        'search/advanced/wildcard_exceptions',
                        'search/advanced/replace_words',
                        'search/advanced/not_words',
                        'search/advanced/long_tail_expressions',
                    ],
                ],
            ]
        );

        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('core_config_data'),
            'config_id',
            'value',
            $queryModifier
        );
    }
}
