<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Setup;

use Magento\Quote\Setup\QuoteSetup as MagentoQuoteSetup;
use Magento\Framework\DB\Ddl\Table;
use Aheadworks\Raf\Api\Data\TotalsInterface;
use Aheadworks\Raf\Api\Data\TotalsItemInterface;

/**
 * Class QuoteSetup
 *
 * @package Aheadworks\Raf\Setup
 */
class QuoteSetup extends MagentoQuoteSetup
{
    /**
     * Retrieve attributes config to install
     *
     * @return array
     */
    public function getAttributesToInstall()
    {
        $attributes = [
            [
                'attribute' => TotalsInterface::AW_RAF_REFERRAL_LINK,
                'type' => 'quote',
                'params' => ['type' => 'varchar']
            ],
            [
                'attribute' => TotalsInterface::AW_RAF_IS_FRIEND_DISCOUNT,
                'type' => 'quote',
                'params' => ['type' => Table::TYPE_SMALLINT]
            ],
            [
                'attribute' => TotalsInterface::AW_RAF_AMOUNT,
                'type' => 'quote',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],
            [
                'attribute' => TotalsInterface::BASE_AW_RAF_AMOUNT,
                'type' => 'quote',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],

            [
                'attribute' => TotalsItemInterface::AW_RAF_RULE_IDS,
                'type' => 'quote_item',
                'params' => ['type' => 'varchar']
            ],
            [
                'attribute' => TotalsItemInterface::AW_RAF_PERCENT,
                'type' => 'quote_item',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],
            [
                'attribute' => TotalsItemInterface::AW_RAF_AMOUNT,
                'type' => 'quote_item',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],
            [
                'attribute' => TotalsItemInterface::BASE_AW_RAF_AMOUNT,
                'type' => 'quote_item',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],

            [
                'attribute' => TotalsInterface::AW_RAF_AMOUNT,
                'type' => 'quote_address',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],
            [
                'attribute' => TotalsInterface::BASE_AW_RAF_AMOUNT,
                'type' => 'quote_address',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],
            [
                'attribute' => TotalsInterface::AW_RAF_SHIPPING_PERCENT,
                'type' => 'quote_address',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],
            [
                'attribute' => TotalsInterface::AW_RAF_SHIPPING_AMOUNT,
                'type' => 'quote_address',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],
            [
                'attribute' => TotalsInterface::BASE_AW_RAF_SHIPPING_AMOUNT,
                'type' => 'quote_address',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],

            [
                'attribute' => TotalsItemInterface::AW_RAF_RULE_IDS,
                'type' => 'quote_address_item',
                'params' => ['type' => 'varchar']
            ],
            [
                'attribute' => TotalsItemInterface::AW_RAF_PERCENT,
                'type' => 'quote_address_item',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],
            [
                'attribute' => TotalsItemInterface::AW_RAF_AMOUNT,
                'type' => 'quote_address_item',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ],
            [
                'attribute' => TotalsItemInterface::BASE_AW_RAF_AMOUNT,
                'type' => 'quote_address_item',
                'params' => ['type' => Table::TYPE_DECIMAL]
            ]
        ];

        return $attributes;
    }

    /**
     * Remove entity attribute. Overwritten for flat entities support
     *
     * @param int|string $entityTypeId
     * @param string $code
     * @return $this
     */
    public function removeAttribute($entityTypeId, $code)
    {
        if (isset($this->_flatEntityTables[$entityTypeId])
            && $this->_flatTableExist($this->_flatEntityTables[$entityTypeId])
        ) {
            $this->removeFlatAttribute($this->_flatEntityTables[$entityTypeId], $code);
        } else {
            parent::removeAttribute($entityTypeId, $code);
        }

        return $this;
    }

    /**
     * Remove attribute as separate column in the table
     *
     * @param string $table
     * @param string $attribute
     * @return $this
     */
    protected function removeFlatAttribute($table, $attribute)
    {
        $tableInfo = $this->getConnection()->describeTable($this->getTable($table));
        if (isset($tableInfo[$attribute])) {
            $this->getConnection()->dropColumn($this->getTable($table), $attribute);
        }

        return $this;
    }
}
