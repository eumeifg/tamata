<?php

namespace Ktpl\CityDropdown\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements  UpgradeSchemaInterface
{
  public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
  {
      $setup->startSetup();

      if (version_compare($context->getVersion(), '1.0.2', '<')) {
        $setup->getConnection()->addColumn(
          $setup->getTable("ktpl_city"),
          'country_id',
          [
            "type" => Table::TYPE_TEXT,
            "nullable" => true,
            "comment" => "country iso code 2"
          ]
        );
      }
      $setup->endSetup();
  }
}