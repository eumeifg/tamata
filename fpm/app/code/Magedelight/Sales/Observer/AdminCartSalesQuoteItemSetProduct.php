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

namespace Magedelight\Sales\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

class AdminCartSalesQuoteItemSetProduct implements ObserverInterface
{
    /**
     * Indicates how to process post data
     */
    private const ACTION_SAVE = 'save';

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $_vendorHelper;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product
     */
    protected $vendorProductResource;

    /**
     * @var mixed
     */
    protected $serializer;

    /**
     * AdminCartSalesQuoteItemSetProduct constructor.
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magedelight\Catalog\Helper\Data $mdCatalogHelper
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Backend\Model\Session\Quote $quoteSession
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Catalog\Helper\Data $mdCatalogHelper,
        \Magento\Sales\Model\Order $order,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        Json $serializer = null
    ) {
        $this->_layout = $layout;
        $this->_request = $request;
        $this->_vendorHelper = $vendorHelper;
        $this->vendorProductResource = $vendorProductResource;
        $this->_vendorRepository = $vendorRepository;
        $this->mdCatalogHelper = $mdCatalogHelper;
        $this->order = $order;
        $this->_session = $quoteSession;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $item = $observer->getQuoteItem();
        $productId = $item->getProductId();

        $optionId = '';
        $vendorId = '';

        /* If prodcut iks configurable then get the child product */
        if ($item->getProductType() == 'configurable') {
            $optionId = $item->getOptionByCode('simple_product')->getValue();
        }

        /* Update existing product in admin cart */
        if ($this->_request->getPost('update_items')) {
            $requestData = $this->_request->getPost('item', []);
            if($item->getParentItemId() != Null){
                $updatedItemData = $requestData[$item->getParentItemId()];
            }else{
                if($item->getItemId()){
                    $updatedItemData = $requestData[$item->getItemId()];
                }
            }
            if(isset($updatedItemData)){
                $vendorId = $updatedItemData['vendor_id'];
            }else{
                if ($optionId) {
                    $vendorId = $this->mdCatalogHelper->getDefaultVendorId($optionId);
                } else {
                    $vendorId = $this->mdCatalogHelper->getDefaultVendorId($productId);
                }
            }
        }

        $action = strtolower($this->_request->getActionName());
        if ($this->_request->has('item') && !$this->_request->getPost('update_items')
            && $action !== self::ACTION_SAVE
        ) {
            if ($optionId) {
                $vendorId = $this->mdCatalogHelper->getDefaultVendorId($optionId);
            } else {
                $vendorId = $this->mdCatalogHelper->getDefaultVendorId($productId);
            }
        }else if($action == "reorder"){
            $isReorder = $this->order->getReordered();
            $order = $this->order->load($this->_request->getParam('order_id'));
            $allOrderItems = $order->getAllItems();
            foreach($allOrderItems as $key => $orderItem){
                if($orderItem->getProductId() == $item->getProductId()){
                    if ($orderItem->getProductType() == 'configurable') {
                        foreach($orderItem->getChildrenItems() as $orderChildren){
                            if($orderChildren->getProductId() == $optionId){
                                $vendorId = $orderChildren->getVendorId();
                            }
                        }
                    }else{
                        $vendorId = $orderItem->getVendorId();
                    }
                }
            }
        }

        if ($optionId) {
            $price = $this->mdCatalogHelper->getVendorFinalPrice($vendorId, $optionId);
        } else {
            $price = $this->mdCatalogHelper->getVendorFinalPrice($vendorId, $productId);
        }

        if ($vendorId) {
            $item = ($item->getParentProductId()) ? $item->getParentItem() : $item;
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->setVendorId($vendorId);

            /* Add/Update Vendor SKU */
            if ($item->getProductType() === 'configurable') {
                $item->setVendorSku(
                    $this->vendorProductResource->getVendorProductSku($item->getProductId(), null)
                );
            } else {
                $item->setVendorSku(
                    $this->vendorProductResource->getVendorProductSku($item->getProductId(), $vendorId)
                );
            }

            /* Add/Update Vendor Information in additional options */
            $soldBy = $this->_vendorHelper->getVendorNameById($vendorId);
            $additionalOptions = [];
            if ($item->getOptionByCode('additional_options')) {
                $additionalOptions = $this->serializer->unserialize($item->getOptionByCode('additional_options')->getValue());
            }

            if (!empty($additionalOptions)) {
                foreach($additionalOptions as $key => $additionalOption){
                    if (!in_array('vendor', array_column($additionalOption, 'code'))) {
                        unset($additionalOptions[$key]);
                        $additionalOptions[] = [
                            'code' => 'vendor',
                            'label' => __('Sold By'),
                            'value' => $soldBy
                        ];
                    }
                }
            } else {
                $additionalOptions[] = [
                    'code' => 'vendor',
                    'label' => __('Sold By'),
                    'value' => $soldBy
                ];
            }
            /* add the additional options array with the option code additional_options*/
            $item->addOption(['product_id' => $item->getProductId(),
                'code' => 'additional_options',
                'value' => $this->serializer->serialize($additionalOptions)]);

        }

    }
}