<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Quote
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Quote\Plugin;

use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;

class QuoteTotalPlugin
{
    /**
     * @var \Magento\Quote\Api\Data\TotalsItemExtensionFactory
     */
    protected $totalItemExtension;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $itemCollection;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;   

    protected $shippingMethodManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

     /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var \Magento\Quote\Model\ShippingAssignmentFactory
     */
    protected $shippingAssignmentFactory;

    /**
     * @var \Magento\Quote\Model\ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * QuoteTotalPlugin constructor.
     * @param \Magento\Quote\Api\Data\TotalsItemExtensionFactory $totalItemExtension
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollection
     * @param \Magento\Catalog\Helper\Product $productHelper
     */
    public function __construct(
        \Magento\Quote\Api\Data\TotalsItemExtensionFactory $totalItemExtension,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollection,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        AddressInterface $addressInterface,        
        ShippingMethodManagement $shippingMethodManagement,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        BillingAddressManagementInterface $billingAddressManagement,
        CartExtensionFactory $cartExtensionFactory = null,
        ShippingAssignmentFactory $shippingAssignmentFactory = null,
        ShippingFactory $shippingFactory = null,
        Logger $logger,
        Config $shippingmodelconfig, 
        ScopeConfigInterface $scopeConfig

    ) {
        $this->totalItemExtension = $totalItemExtension;
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
        $this->itemCollection = $itemCollection;
    }
    
     /**
      * Add attribute values
      *
      * @param   \Magento\Quote\Api\CartTotalRepositoryInterface $subject,
      * @param   $quote
      * @return  $quoteData
      */
    public function afterGet(CartTotalRepositoryInterface $subject, $result, $cartId)
    {
        $result = $this->setAttributeValue($result);
        return $result;
    }
    
    /**
     * set value of attributes
     *
     * @param   $product,
     * @return  $extensionAttributes
     */
    protected function setAttributeValue($cartTotalData)
    {
        if (($cartTotalData->getData())) {
            foreach ($cartTotalData->getItems() as $item) {
                $extensionAttributes = $this->totalItemExtension->create();
                $quoteItem = $this->itemCollection->create()
                    ->addFieldToFilter('item_id', $item->getItemId())
                    ->addFieldToSelect(['product_id','sku','vendor_id'])
                    ->getFirstItem();
                $productData = $this->productRepository->create()->getById($quoteItem->getProductId());
                $extensionAttributes->setImage($this->productHelper->getImageUrl(
                    $productData
                ));
                $extensionAttributes->setSku($quoteItem->getSku());
                $extensionAttributes->setProductId($quoteItem->getProductId());
                $extensionAttributes->setVendorId($quoteItem->getVendorId());
                $item->setExtensionAttributes($extensionAttributes);
            }
            return $cartTotalData;
        }
    }
}
