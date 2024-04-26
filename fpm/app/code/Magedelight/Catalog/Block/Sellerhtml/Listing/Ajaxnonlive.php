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
namespace Magedelight\Catalog\Block\Sellerhtml\Listing;

use Magedelight\Catalog\Model\ProductRequest as VendorProductRequest;

class Ajaxnonlive extends AbstractAjaxProduct
{

    /**
     *
     * @var VendorProductRequest
     */
    protected $_productRequest;

    /**
     *
     * @return VendorProductRequest
     */
    public function getCollection()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$this->_productRequest) {
            $extparam = $this->getRequest()->getParam('extparam');
            if ($extparam == 1) {
                $collection = $this->vendorProductFactory->create()->getCollection()
                    ->addFieldToFilter('main_table.vendor_product_id', $id);
                $collection->getSelect()->joinLeft(
                    ['cpe' => 'catalog_product_entity'],
                    "main_table.marketplace_product_id = cpe.entity_id",
                    ['sku']
                )->distinct(true);
                $this->_productRequest = $collection->getFirstItem();
            } else {
                $this->_productRequest = $this->_vendorProductRequestFactory->create()->load($id);
            }
        }
        return $this->_productRequest;
    }

    /**
     *
     * @return boolean
     */
    public function isRejectedProductRequest()
    {
        return intval($this->getCollection()->getData('status')) === VendorProductRequest::STATUS_DISAPPROVED;
    }

    public function getSubmitUrlNonLive()
    {
        return $this->getUrl('rbcatalog/listing/nonlivesubmit/');
    }
}
