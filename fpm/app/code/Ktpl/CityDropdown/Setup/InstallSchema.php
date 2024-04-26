<?php

namespace Ktpl\CityDropdown\Setup;

use Ktpl\CityDropdown\Api\Data\CityInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

//@codingStandardsIgnoreFile

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    const TABLE = 'ktpl_city';

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $romCityTable = $setup->getTable(self::TABLE);

        if (!$setup->tableExists($romCityTable)) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable(self::TABLE)
            )->addColumn(
                CityInterface::ENTITY_ID,
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'City Id'
            )->addColumn(
                CityInterface::CITY_NAME,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'City Name'
            );
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}