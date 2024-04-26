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
namespace Magedelight\Catalog\Block\Sellerhtml\Sellexisting;

use Magedelight\Backend\Block\Template;

class VendorListing extends Template
{
    public function __construct(
        Template\Context $context,
        \Magedelight\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);        
        $this->_productFactory = $productFactory;
        $this->_coreRegistry = $coreRegistry;
    }
    public function getVendorData()
    {
        $productId  = $this->getRequest()->getParam('id', false);

        $collection = $this->_productFactory->create()->getCollection()
            ->addFieldToFilter('marketplace_product_id', $productId);
        $collection->getSelect()->joinLeft(
            ['oce' => 'md_vendor'],
            "main_table.vendor_id = oce.vendor_id",
            [
                'oce.name'
            ]
        );
        $collection->getSelect()->joinLeft(
            ['ocer' => 'md_vendor_rating'],
            "main_table.vendor_id = ocer.vendor_id",
            [
                'ocer.vendor_rating_id'
             ]
        );

        $collection->getSelect()->joinLeft(
            ['ocert' => 'md_vendor_rating_rating_type'],
            "ocer.vendor_rating_id = ocert.vendor_rating_id",
            [
               'ROUND(SUM(`ocert`.`rating_avg`)/(SELECT  count(*) FROM md_vendor_rating where vendor_id=main_table.vendor_id)) as rating_avg'
            ]
        )->group('main_table.vendor_id');

        $collection->addFieldToFilter('main_table.status', ['eq' => 1]);
        $collection->addFieldToFilter('main_table.is_deleted', ['eq' => 0]);

        /* Order by highest rating and lowest price*/
        $collection->getSelect()->order('ocert.rating_avg DESC');
        $collection->getSelect()->order('main_table.price ASC');

        return $collection;
    }

    public function getCurrentProductId()
    {
        return $this->_coreRegistry->registry('current_product')->getId();
    }
}
