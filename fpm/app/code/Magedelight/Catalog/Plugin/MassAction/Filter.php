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
namespace Magedelight\Catalog\Plugin\MassAction;

use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;
use Magento\Framework\Data\Collection\AbstractDb;

class Filter
{
    /**
     * @var Magedelight\Catalog\Model\Product
     */
    private $_vendorProduct;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var FlatState
     */
    protected $flatState;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magedelight\Catalog\Model\ProductFactory $vendorProduct,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request,
        FlatState $flatState
    ) {
        $this->flatState = $flatState;
        $this->_vendorProduct = $vendorProduct;
        $this->_messageManager = $messageManager;
        $this->request = $request;
    }

    public function afterGetCollection(
        \Magento\Ui\Component\MassAction\Filter $subject,
        $result,
        AbstractDb $collection
    ) {
        $action     = $this->request->getActionName();
        if ($collection instanceof \Magedelight\Catalog\Model\ResourceModel\Product\Core\Collection &&
            $action == 'massDelete') {
            $ids = $collection->getAllIds();
            $productsWithVendors = $this->getProductWithVendors($ids);
            if ($productsWithVendors && !empty($productsWithVendors)) {
                /* Flat table Compatibility Changes */
                if ($this->flatState->isAvailable()) {
                    $collection->addFieldToFilter("entity_id", ['nin' => $productsWithVendors]);
                } else {
                    $collection->addAttributeToFilter("entity_id", ['nin' => $productsWithVendors]);
                }
                $this->_messageManager->addNotice(__("Some products were not deleted due to vendor association"));
            }
        }
        return $collection;
    }

    private function getProductWithVendors($ids)
    {
        $productsWithVendors = [];
        $collection = $this->_vendorProduct->create()->getCollection()
            ->addFieldToFilter('marketplace_product_id', ['in' => $ids]);
        $productsWithVendors = $collection->getColumnValues('marketplace_product_id');
        return array_unique($productsWithVendors);
    }
}
