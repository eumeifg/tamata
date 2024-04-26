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
namespace Magedelight\Catalog\Controller\Cart\Plugin;

class BeforeAddProductToCart
{
    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_cart = $cart;
        $this->_helper = $helper;
        $this->request = $request;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\Add $subject
     * @return void
     */
    public function beforeExecute(\Magento\Checkout\Controller\Cart\Add $subject)
    {
        if ($this->_helper->isEnabled()) {
            $vendorId = $this->request->getPostValue('vendor_id');
            $optionId = $subject->getRequest()->getParam('super_attribute', false);

            $productId = $subject->getRequest()->getParam('product', false);

            $simpleProductId = $subject->getRequest()->getParam('simple_product', false);

            $ischild = ($optionId && $simpleProductId) ? true : false;

            if (isset($vendorId)) {
                $vendorId = $this->request->getPostValue('vendor_id');
            } else {
                if ($subject->getRequest()->getParam('vendor', false) ||
                    $subject->getRequest()->getParam('vendor_id', false)
                ) {
                    $vendorId = ($subject->getRequest()->getParam('vendor', false)) ?
                        $subject->getRequest()->getParam('vendor', false) :
                        $subject->getRequest()->getParam('vendor_id', false);
                } elseif ($productId && empty($subject->getRequest()->getParam('vendor'))) {
                    $vendorId = $this->_helper->getDefaultVendorId($productId);
                } else {
                    return;
                }
            }

            if ($simpleProductId) {
                $this->_checkoutSession->setOptionId($simpleProductId);
            } else {
                $this->_checkoutSession->setOptionId(null);
            }

            $this->_checkoutSession->setProductVendorId($vendorId);
            $availableQty = $this->_checkItemQtyAvailability(
                $vendorId,
                $productId,
                $subject->getRequest()->getParam('qty'),
                $this->_helper->getVendorQty($vendorId, (($ischild) ? $simpleProductId : $productId))
            );

            $this->_checkoutSession->unsQty();
            $this->_checkoutSession->setQty($subject->getRequest()->getParam('qty'));
            $subject->getRequest()->setParams(['qty' => $availableQty]);
            $this->_checkoutSession->setQtyToAdd($availableQty);
        }
    }

    /**
     * count total qty of item of vendor and calculate available qty of item from same vendor.
     * @param bool $vendorId
     * @param bool $productId
     * @param int $qtyRequest
     * @param int $vendorQty
     * @return int $qtyExist
     */
    protected function _checkItemQtyAvailability($vendorId = false, $productId = false, $qtyRequest = 0, $vendorQty = 0)
    {
        $cart = $this->_cart->getQuote();
        $qtyExist = null;
        $itemTotalQty = 0;

        if ($cart->getItemsCount()) {
            foreach ($cart->getAllItems() as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }

                if (($item->getProductId() == $productId) && ($item->getVendorId() == $vendorId)) {
                    $itemTotalQty += $item->getQty();
                }
            }
            $finalAvailable = $vendorQty - $itemTotalQty;

            if ($qtyRequest > $finalAvailable) {
                $qtyExist = $finalAvailable;
            } else {
                $qtyExist = $qtyRequest;
            }
        }

        if ($qtyExist === null) {
            $qtyExist = ($vendorQty >= $qtyRequest) ? $qtyRequest : ($qtyRequest - ($qtyRequest - $vendorQty));
        }
        return $qtyExist;
    }
}
