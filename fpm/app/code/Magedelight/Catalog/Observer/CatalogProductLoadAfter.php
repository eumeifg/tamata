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
namespace Magedelight\Catalog\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

class CatalogProductLoadAfter implements ObserverInterface
{

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $_vendorHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    protected $serializer;

    /**
     * @var \Magento\Wishlist\Model\Item
     */
    protected $_wishlistItem;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $areaCode;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @param \Magento\Framework\App\State $areaCode
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Json $serializer
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Wishlist\Model\Item $wishlistItem,
        \Magento\Framework\App\State $areaCode,
        \Magento\Checkout\Model\Session $checkoutSession,
        Json $serializer = null
    ) {
        $this->_request = $request;
        $this->_vendorHelper = $vendorHelper;
        $this->cart = $cart;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->helper = $helper;
        $this->_wishlistItem = $wishlistItem;
        $this->areaCode = $areaCode;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnabled()) {
            if ($this->_request->getActionName() == 'add' ||
                $this->_request->getActionName() == 'fromcart' ||
                $this->_request->getActionName() == 'reorder' ||
                $this->_request->getActionName() == 'updateItemOptions' ||
                $this->_request->getActionName() == 'updateFailedItemOptions' ||
                $this->_request->getActionName() == 'wishlist' ||
                $this->_request->getActionName() == 'cart') {
                if ($this->_request->getActionName() == 'fromcart') {
                    $item = $this->cart->getQuote()->getItemById($this->_request->getParam('item'));
                    $vendorId = $item->getVendorId();
                } elseif ($this->_request->getActionName() == 'wishlist') {
                    $itemId = $this->_request->getParam('item');
                    $wishlistItem = $this->_wishlistItem->loadWithOptions(
                        $itemId,
                        'info_buyRequest'
                    );
                    $vendorId = $wishlistItem->getBuyRequest()->getVendorId();
                } elseif ($this->_request->getParam('vendor_id')) {
                    $vendorId = $this->_request->getParam('vendor_id');
                } else {
                    $vendorId = $this->_request->getParam('v');
                }

                if ($vendorId) {
                    $this->setVendorInformation($observer, $vendorId);
                }
            } elseif ($this->areaCode->getAreaCode() == "webapi_rest") {
                $vendorId = $this->checkoutSession->getVendorId();
                if ($vendorId) {
                    $this->setVendorInformation($observer, $vendorId);
                }
            }
        }
    }

    /**
     * @param $observer
     * @param $vendorId
     */
    public function setVendorInformation($observer, $vendorId)
    {
        $product = $observer->getProduct();
        /*add to the additional options array*/
        $additionalOptions = [];
        if ($additionalOption = $product->getCustomOption('additional_options')) {
            $additionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
        }
        if (!empty($additionalOptions)) {
            if (!in_array('vendor', array_column($additionalOptions, 'code'))) {
                $additionalOptions[] = [
                    'code'  => 'vendor',
                    'label' => __('Sold By'),
                    'value' => $this->_vendorHelper->getVendorNameById($vendorId)
                ];
            }
        } else {
            $additionalOptions[] = [
                'code'  => 'vendor',
                'label' => __('Sold By'),
                'value' => $this->_vendorHelper->getVendorNameById($vendorId)
            ];
        }
        /* add the additional options array with the option code additional_options*/
        $observer->getProduct()->addCustomOption(
            'additional_options',
            $this->serializer->serialize($additionalOptions)
        );
    }
}
