<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Sellerhtml\Dashboard\Products;

class LowStock extends \Magedelight\Vendor\Block\Sellerhtml\Dashboard\AbstractDashboard
{
     
    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Reports\Product\CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;
    
    protected $productLowStock = null;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magedelight\Catalog\Model\ResourceModel\Reports\Product\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magedelight\Catalog\Model\ResourceModel\Reports\Product\CollectionFactory $collectionFactory,
        \Magedelight\Catalog\Model\Product $vendorProduct,
        array $data = []
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_collectionFactory = $collectionFactory;
        $this->vendorProduct = $vendorProduct;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function getCollection()
    {
        return $this->_collectionFactory->create();
    }

    public function getLowInventoryProducts()
    {
        
        if ($this->productLowStock == null) {
            $this->productLowStock = $this->getCollection()->calculateLowInventoryProducts();
            $this->vendorProduct->addProductData($this->productLowStock);
        }
        $this->productLowStock->getSelect()->where('main_table.qty <= rbvpw.reorder_level');
        $this->productLowStock->getSelect()->group('main_table.marketplace_product_id');
        return $this->productLowStock;
    }
    
    public function getLiveProductsUrl()
    {
        return $this->getUrl('rbcatalog/listing/index', ['tab' => '1,0', 'ostock' => 1, 'sfrm' => 'l']);
    }
}
