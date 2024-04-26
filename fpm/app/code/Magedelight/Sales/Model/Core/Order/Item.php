<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Sales\Model\Core\Order;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\AttributeValueFactory;

class Item extends \Magento\Sales\Model\Order\Item
{

    /**
     * @var \Magedelight\Catalog\Api\ProductRepositoryInterface
     */
    protected $vendorProductRepository;

    /**
     * @var \Magedelight\Sales\Model\ResourceModel\Order
     */
    protected $vendorOrderResource;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Sales\Model\ResourceModel\Order $vendorOrderResource
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Sales\Model\ResourceModel\Order $vendorOrderResource,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->vendorProductFactory = $vendorProductFactory;
        $this->vendorOrderResource = $vendorOrderResource;
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $orderFactory,
            $storeManager,
            $productRepository,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Retrieve product
     *
     * @param $vendorId
     * @return \Magento\Catalog\Model\Product|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorProduct($vendorId = null)
    {
        if (!$this->hasData('vendor_product')) {
            try {
                $type  = ($this->getProduct()) ? $this->getProduct()->getTypeId() : null;
                $vendorId = ($vendorId) ? $vendorId : $this->getVendorId();
                if ($this->getProductId()) {
                    $vendorProduct = null;
                    if ($type == Configurable::TYPE_CODE) {
                        /* Configurable product can be created by vendor A and is created once and offers can be
                         * added on child by vendor B.Vendor Id will not match for orders placed on vendor B
                         * product as Parent product was created by vendor A, which will further not return vendor sku.
                         * So avoid passing $vendorId in order to fetch Vendor SKU.
                         */
                        $vendorProduct = $this->vendorProductFactory->create()
                            ->getVendorProduct(false, $this->getProductId(), false, false);
                    } else {
                        if ($vendorId) {
                            $vendorProduct = $this->vendorProductFactory->create()
                                ->getVendorProduct($vendorId, $this->getProductId(), false, false);
                        }
                    }
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $noEntityException) {
                $vendorProduct = null;
            }
            $this->setVendorProduct($vendorProduct);
        }
        return $this->getData('vendor_product');
    }

    /**
     * Return discount_amount
     *
     * @return float|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDiscountAmount()
    {
        if ($this->getDiscountFlag()) {
            return  $this->vendorOrderResource->getDiscountAmount($this->getVendorOrderId());
        } else {
            return parent::getDiscountAmount();
        }
    }

    /**
     * @return array|null
     */
    public function getVendorOrder()
    {
        if (!$this->hasData('vendor_order')) {
            try {
                $vendorOrder = $this->vendorOrderRepository->getById($this->getVendorOrderId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $noEntityException) {
                $vendorOrder = null;
            }
            $this->setVendorOrder($vendorOrder);
        }
        return $this->getData('vendor_order');
    }
}
