<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * RMA Helper
 */
namespace Magedelight\Rma\Helper;

use Magento\Framework\DB\Select;
use Magento\Rma\Model\Rma;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * @api
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Variable to contain order items collection for RMA creating
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    protected $_orderItems = null;

    /**
     * Rma item factory
     *
     * @var \Magento\Rma\Model\ResourceModel\ItemFactory
     */
    protected $_itemFactory;

    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Admin\Item
     */
    protected $adminOrderItem;
    
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Rma\Model\ResourceModel\ItemFactory $itemFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Admin\Item $adminOrderItem
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Rma\Model\ResourceModel\ItemFactory $itemFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Admin\Item $adminOrderItem,
        \Magedelight\Sales\Api\OrderRepositoryInterface $orderRepository,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->_itemFactory = $itemFactory;
        $this->_customerSession = $customerSession;
        $this->adminOrderItem = $adminOrderItem;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * Checks whether RMA module is enabled for frontend in system config
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            Rma::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Checks for ability to create RMA
     *
     * @param  int|\Magento\Sales\Model\Order $order
     * @param  bool $forceCreate - set yes when you don't need to check config setting (for admin side)
     * @return bool
     */
    public function canCreateRma($orderId, $vendorId, $forceCreate = false, $itemId = null)
    {
        if (is_object($orderId)) {
            $orderId = $orderId->getId();
        }
        if (!is_numeric($orderId)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please check this order for errors.'));
        }

        $returnableItems  = $this->_itemFactory->create()->getReturnableItems($orderId);
        $canReturn = isset($returnableItems[$itemId]);
        
        if ($canReturn && ($forceCreate || $this->isEnabled())) {
            return true;
        }
        return false;
    }
    
    /**
     * Get url for rma create
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getReturnCreateUrl($order, $vendorId)
    {
        if ($this->_customerSession->isLoggedIn()) {
            return $this->_getUrl('rma/returns/create', ['order_id' => $order->getId(), 'vendor_id' => $vendorId]);
        } else {
            return $this->_getUrl('rma/guest/create', ['order_id' => $order->getId(), 'vendor_id' => $vendorId]);
        }
    }

    /**
     * @param $productId
     * @return \Magento\Framework\DataObject[]
     */
    protected function getOrderedProducts($productId)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns($collection->getIdFieldName());

        $collection->addAttributeToSelect('is_returnable');
        $collection->addFieldToFilter($collection->getIdFieldName(), ['in' => [$productId]]);
        return $collection->getItems();
    }
     
    /**
     * Get whether selected product is returnable
     * @param type $productId
     * @param type $storeId
     * @return boolean
     */
    public function canReturnProduct($productId, $storeId = null)
    {
        $orderProducts = $this->getOrderedProducts($productId);
        $product = $orderProducts[$productId];
        $isReturnable = $product->getIsReturnable();
        
        if ($isReturnable === null) {
            $isReturnable = \Magento\Rma\Model\Product\Source::ATTRIBUTE_ENABLE_RMA_USE_CONFIG;
        }
        switch ($isReturnable) {
            case \Magento\Rma\Model\Product\Source::ATTRIBUTE_ENABLE_RMA_YES:
                return true;
            case \Magento\Rma\Model\Product\Source::ATTRIBUTE_ENABLE_RMA_NO:
                return false;
            default:
                //Use config and NULL
                return $this->scopeConfig->getValue(
                    \Magento\Rma\Model\Product\Source::XML_PATH_PRODUCTS_ALLOWED,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
        }
    }

    /**
     * @param string $_item
     * @return string
     */
    function isReturnAllowed($_item)
    {
        $orderStatus = $this->orderRepository->getById($_item->getVendorOrderId())->getStatus();
        $canCreateRMA = $this->canCreateRma($_item->getOrder(), $_item->getVendorId(), false, $_item->getId());

        if ($canCreateRMA) {
            $canRProduct = $this->canReturnProduct($_item->getProductId());
            if (strtolower($orderStatus) == VendorOrder::STATUS_COMPLETE &&
                $canRProduct
            ) {
                return true;
            }
        }
        return false;
    }
}
