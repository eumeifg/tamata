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
namespace Magedelight\Sales\Block\Vendor\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;

/**
 * Sales order history block(Don't take file from this location
 * You can take it from reorder module)
 */
class History extends \Magento\Sales\Block\Order\History
{

    /**
     * @var \Magedelight\Sales\Helper\Data
     */
    protected $salesHelper;

    protected $_template = "Magedelight_Sales::order/history.phtml";

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $orders;

    /**
     * @var CollectionFactoryInterface
     */
    private $orderCollectionFactory;
    private $stockItem;
    protected $_productloader;
    protected $_vendorFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockItem
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magedelight\Sales\Helper\Data $salesHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Sales\Helper\Data $salesHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $orderCollectionFactory,
            $customerSession,
            $orderConfig,
            $data
        );
        $this->stockItem = $stockItem;
        $this->_productloader = $_productloader;
        $this->_vendorFactory = $vendorFactory;
        $this->imageHelper = $imageHelper;
        $this->salesHelper = $salesHelper;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Orders'));
    }

    /**
     * @return CollectionFactoryInterface
     *
     * @deprecated
     */
    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            $collection  = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'main_table.status',
                ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
            );

            $collection->getSelect()->joinLeft(
                ['rvo'=> 'md_vendor_order'],
                '(rvo.order_id = main_table.entity_id AND rvo.status IN ("shipped","in_transit"))',
                ['vendor_orders_in_process'=>'count(rvo.status)']
            )->group('main_table.entity_id');
            $collection->setOrder(
                'main_table.created_at',
                'desc'
            );
            $this->orders = $collection;
        }
        return $this->orders;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'sales.reorder.history.pager'
            )->setCollection(
                $this->getOrders()
            );
            $this->setChild('pager', $pager);
            $this->getOrders()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getTrackUrl($order)
    {
        return $this->getUrl('sales/order/track', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function getStockQty($productId, $item)
    {
        $product = $this->_productloader->create()->load($productId);
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $_configChild = $product->getTypeInstance()->getUsedProductIds($product);
            $getChildId = [];
            $productSku = $this->getProductSku($item);
            foreach ($_configChild as $child) {
                $product = $this->_productloader->create()->load($child);
                if ($productSku == $product->getSku()) {
                    return $StockState = $this->stockItem->getStockQty(
                        $product->getId(),
                        $product->getStore()->getWebsiteId()
                    );
                }
            }
        } else {
            return $StockState = $this->stockItem->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
        }
    }

    public function getVendorName($vendorId)
    {
        $vendor = $this->_vendorFactory->create()->load($vendorId);
        return $vendor->getName();
    }

    public function postActionUrl()
    {
        return $this->getUrl("rbquickreorder/index/addProductToCart/");
    }

    public function getProductSku($item)
    {
        $options = $item->getProductOptions();
        if (array_key_exists('simple_sku', $options)) {
            return $options['simple_sku'];
        }
        return false;
    }

    public function getItemOptions($item)
    {
        $options = $item->getProductOptions();
        if (array_key_exists('attributes_info', $options)) {
            return $options['attributes_info'];
        }
        return false;
    }

    public function getProductImage($productId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $this->_productloader->create()->load($productId);
        $image_url = $this->imageHelper
            ->init($product, 'cart_page_product_thumbnail')
            ->setImageFile($product->getFile())
            ->getUrl();

        return $image_url;
    }

    public function getProductUrl($productId)
    {
        $product = $this->_productloader->create()->load($productId);
        return $product->getUrlModel()->getUrl($product);
    }

    /**
     *
     * @return bool
     */
    public function isMagentoOrderStatusDisplayed()
    {
        return $this->salesHelper->showMainOrderStatus();
    }
}
