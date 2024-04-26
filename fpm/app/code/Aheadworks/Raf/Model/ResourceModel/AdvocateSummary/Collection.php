<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\ResourceModel\AdvocateSummary;

use Aheadworks\Raf\Api\Data\AdvocateSummaryInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Aheadworks\Raf\Model\ResourceModel\AdvocateSummary as ResourceAdvocateSummary;
use Aheadworks\Raf\Model\AdvocateSummary;

/**
 * Class Collection
 *
 * @package Aheadworks\Raf\Model\ResourceModel\AdvocateSummary
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(AdvocateSummary::class, ResourceAdvocateSummary::class);
        $this->addFilterToMap('customer_email', 'email');
        $this->addFilterToMap('customer_name', 'name');
        $this->addFilterToMap('website_id', 'main_table.website_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['customer_grid_table' => $this->getTable('customer_grid_flat')],
            'main_table.customer_id = customer_grid_table.entity_id',
            ['customer_email' => 'email', 'customer_name' => 'name']
        );
        return $this;
    }

    /**
     * Add expire filter to collection
     *
     * @param string $expiredDate
     * @return $this
     */
    public function addExpireFilter($expiredDate)
    {
        $this->addFieldToFilter(AdvocateSummaryInterface::EXPIRATION_DATE, ['notnull' => true]);
        $this->getSelect()->where('DATE(' . AdvocateSummaryInterface::EXPIRATION_DATE . ') < ?', $expiredDate);

        return $this;
    }
}
