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
namespace MDC\Sales\Model\Plugin\Quote\Item;

use Magento\GiftCard\Model\Catalog\Product\Type\Giftcard;
use Magento\Checkout\Model\Session;

/**
 * Description of Quote
 *
 * @author Rocket Bazaar Core Team
 */
class Processor
{
    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $_vendorRepository;

    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Magedelight\Vendor\Model\Vendor
     */
    protected $_vendor;

    /**
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param Session $session
     * @param \Magento\Framework\App\State $state
     * @param \Magedelight\Catalog\Model\Product $product
     */
    public function __construct(
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Magedelight\Vendor\Model\VendorFactory $vendor,
        \Magedelight\Catalog\Helper\Data $helper,
        Session $session,
        \Magento\Framework\App\State $state,
        \Magedelight\Catalog\Model\Product $product
    ) {
        $this->_session = $session;
        $this->_vendorRepository = $vendorRepository;
        $this->_vendor = $vendor;
        $this->_cart = $cart;
        $this->_helper = $helper;
        $this->_state = $state;
        $this->product = $product;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\Processor $subject
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Catalog\Model\Product $candidate
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforePrepare(
        \Magento\Quote\Model\Quote\Item\Processor $subject,
        \Magento\Quote\Model\Quote\Item $item,
        \Magento\Framework\DataObject $request,
        \Magento\Catalog\Model\Product $candidate
    ) {
        if (in_array($this->_state->getAreaCode(), [\Magento\Framework\App\Area::AREA_FRONTEND])) {

            if ($candidate->getTypeId() == Giftcard::TYPE_GIFTCARD) {
                $productIds = $candidate->getId();
                $vendor = $this->getGiftCardProductDefaultVendor();
                $item->setVendorId($vendor->getVendorId());
                return [$item, $request, $candidate];
            }

            $vendorId = null;
            $requestedQty = $request->getData('qty');

            if ($candidate->getTypeId() == "configurable") {
                if ($request->getData('simple_product')) {
                    $productIds = $request->getData('simple_product');
                    $vendor = $this->product->getProductDefaultVendor(false, $request->getData('simple_product'));
                } else {
                    /* We had added this code due to gift wrapper does not working for configuration product.*/
                    $quoteItem = $this->_session->getQuote()->getAllVisibleItems();
                    $productIds = '';
                    foreach ($quoteItem as $item1) {
                        $productId = $item1->getProduct()->getId();
                        if ($option = $item1->getOptionByCode('simple_product')) {
                            $productId = $option->getProduct()->getId();
                        }
                        $productIds = $productId;
                    }
                    if ($productIds != '') {
                        $vendor = $this->product->getProductDefaultVendor(false, $productIds);
                    }
                }
            } else {
                $productIds = $candidate->getId();
                $vendor = $this->product->getProductDefaultVendor(false, $candidate->getId());

            }

            if ($request->getData('vendor_id') && $request->getData('vendor_id') != '') {

                $vendorId = $request->getData('vendor_id');
                $item->setVendorId($request->getData('vendor_id'));
            } elseif (!empty($vendor) && $vendor->getVendorId()) {
                $vendorId = $vendor->getVendorId();
                $item->setVendorId($vendor->getVendorId());

            } else {
                $item->setHasError(1);
                $item->setMessage(__("No Vendor found for the item '%1'.", $item->getName()));
            }


            $availableQty = $this->_checkItemQtyAvailability(
                $vendorId,
                $productIds,
                $requestedQty,
                $this->_helper->getVendorQty($vendorId, $productIds)
            );
            $this->_session->setQtyToAdd($availableQty);

            $qtyToAdd = $availableQty;
            if (($qtyToAdd === null)) {
                $qtyToAdd = 1;
            }
            $vendor = $this->_vendorRepository->getById($vendorId);
            $vendorName = $vendor->getBusinessName();

            if ($qtyToAdd <= 0) {
                $erroMsg = __('The product %1 is not available in the requested quantity from %2, you may choose some other vendor.', $item->getProduct()->getName(), $vendorName);
                throw new \Magento\Framework\Exception\LocalizedException(__($erroMsg));
            } elseif ($qtyToAdd < $requestedQty) {
                $erroMsg = __('The Requested quantity is not available from %1, you may add %2 quantity only. You may choose other vendor if available for more quantity.', $vendorName, $qtyToAdd + $item->getQty());
                throw new \Magento\Framework\Exception\LocalizedException(__($erroMsg));
            }
            return [$item, $request, $candidate];
            //}

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

        if (($qtyExist === null)) {
            $qtyExist = ($vendorQty >= $qtyRequest) ? $qtyRequest : ($qtyRequest - ($qtyRequest - $vendorQty));
        }
        return $qtyExist;
    }

    /**
     *
     * @return array
     */
    public function getGiftCardProductDefaultVendor() {

        $vendorData = $this->_vendor->create();
        $collection = $vendorData->getCollection()->addFieldToFilter('is_system', array('eq' => 1));

        if (!$collection->getSize()) {
            $erroMsg = __('Default vendor not exist.');
            throw new \Magento\Framework\Exception\LocalizedException(__($erroMsg));
        }

        return $collection->getFirstItem();

    }
}
