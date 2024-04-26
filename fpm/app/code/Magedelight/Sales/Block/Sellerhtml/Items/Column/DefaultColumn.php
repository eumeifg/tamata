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
namespace Magedelight\Sales\Block\Sellerhtml\Items\Column;

use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;

/**
 * sales order column renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class DefaultColumn extends \Magedelight\Sales\Block\Sellerhtml\Items\AbstractItems
{
    /**
     * Option factory
     *
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $_optionFactory;

    /**
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        array $data = []
    ) {
        $this->_optionFactory = $optionFactory;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
    }

    /**
     * Get item
     *
     * @return Item|QuoteItem
     */
    public function getItem()
    {
        $item = $this->_getData('item');
        if ($item instanceof Item || $item instanceof QuoteItem) {
            return $item;
        } else {
            return $item->getOrderItem();
        }
    }

    /**
     * Get order options
     *
     * @return array
     */
    public function getOrderOptions()
    {
        $result = [];
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }

    /**
     * Return custom option html
     *
     * @param array $optionInfo
     * @return string
     */
    public function getCustomizedOptionValue($optionInfo)
    {
        // render customized option view
        $_default = $optionInfo['value'];
        if (isset($optionInfo['option_type'])) {
            try {
                $group = $this->_optionFactory->create()->groupFactory($optionInfo['option_type']);
                return $group->getCustomizedView($optionInfo);
            } catch (\Exception $e) {
                return $_default;
            }
        }
        return $_default;
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getItem()->getSku();
    }

    /**
     * Calculate total amount for the item
     *
     * @param QuoteItem|Item|InvoiceItem|CreditmemoItem $item
     * @return mixed
     */
    public function getTotalAmount($item)
    {
        $totalAmount = $item->getRowTotal() - $item->getDiscountAmount();

        return $totalAmount;
    }

    /**
     * Calculate base total amount for the item
     *
     * @param QuoteItem|Item|InvoiceItem|CreditmemoItem $item
     * @return mixed
     */
    public function getBaseTotalAmount($item)
    {
        $baseTotalAmount =  $item->getBaseRowTotal() - $item->getBaseDiscountAmount();

        return $baseTotalAmount;
    }
}
