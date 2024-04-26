<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\ResourceModel\ProductRequestStore;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * ID Field name
     *
     * @var string
     */
    protected $_idFieldName = 'row_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'md_vendor_product_request_store_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'product_request_store_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magedelight\Catalog\Model\ProductRequestStore::class,
            \Magedelight\Catalog\Model\ResourceModel\ProductRequestStore::class
        );
    }
}
