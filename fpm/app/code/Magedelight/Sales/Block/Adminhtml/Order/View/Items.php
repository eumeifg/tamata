<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Block\Adminhtml\Order\View;

class Items extends \Magento\Sales\Block\Adminhtml\Order\View\Items
{
    /**
     * @var \Magento\Weee\Block\Item\Price\Renderer
     */
    protected $priceRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Weee\Block\Item\Price\Renderer $priceRenderer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Weee\Block\Item\Price\Renderer $priceRenderer,
        array $data = []
    ) {
        $this->priceRenderer = $priceRenderer;
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $data
        );
    }
    public function getItemsCollection()
    {
        $vendorOrder = $this->_coreRegistry->registry('vendor_order');
        if ($vendorOrder) {
            $collection = $this->getOrder()->getItemsCollection();
            foreach ($collection as $item) {
                if ($item->getData('vendor_order_id') == $vendorOrder->getData('vendor_order_id')) {
                    $item->setTotal($this->getTotalAmount($item));
                }
            }
            return $collection;
        } else {
            $collection = parent::getItemsCollection();
        }
        return $collection;
    }

    public function getVendorOrder()
    {
        return $this->_coreRegistry->registry('vendor_order');
    }
    
    /**
     * Return the total amount minus discount
     *
     * @param OrderItem|InvoiceItem|CreditMemoItem $item
     * @return mixed
     */
    protected function getTotalAmount($item)
    {
        return $this->priceRenderer->getTotalAmount($item);
    }
}
