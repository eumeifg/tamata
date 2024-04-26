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

namespace MDC\Catalog\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\GiftCard\Model\Catalog\Product\Type\Giftcard;

class BindVendorPriceToCart implements ObserverInterface
{
    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $_vendorRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product
     */
    protected $vendorProductResource;

    /**
     * BindVendorPriceToCart constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Checkout\Model\Session                   $checkoutSession,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Catalog\Helper\Data                  $helper,
        \Magedelight\Catalog\Model\ResourceModel\Product  $vendorProductResource,
        \Magento\Checkout\Model\Cart                      $cart,
        \Magento\SalesRule\Model\CouponFactory            $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface        $quoteRepository,
        Json                                              $serializer = null
    )
    {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->_checkoutSession = $checkoutSession;
        $this->_vendorRepository = $vendorRepository;
        $this->_helper = $helper;
        $this->vendorProductResource = $vendorProductResource;
        $this->cart = $cart;
        $this->couponFactory = $couponFactory;
        $this->quoteRepository = $quoteRepository;

    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magedelight\Catalog\Observer\BindVendorPriceToCart
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->_helper->isEnabled()) {
                $event = $observer->getEvent();

                if ($event->getProduct()->getTypeId() == Giftcard::TYPE_GIFTCARD) {
                    return $this;
                }
                $vendorId = $this->_checkoutSession->getProductVendorId();
                $this->_checkoutSession->unsProductVendorId();
                $optionId = $this->_checkoutSession->getOptionId();
                $this->_checkoutSession->unsOptionId();

                $item = $event->getQuoteItem();

                /* Handle specific case for reorder */
                if (!$vendorId) {
                    $vendorId = $item->getVendorId();
                }

                $product = $event->getProduct();
                $productId = $product->getId();
                $productName = $product->getName();

                if (!$vendorId) {
                    $errorMessage = __('No vendor found for this product.');
                    throw new \Magento\Framework\Exception\LocalizedException(__($errorMessage));
                }

                $vendor = $this->_vendorRepository->getById($vendorId);
                $vendorName = $vendor->getBusinessName();

                $productUrl = $product->getProductUrl() . ($vendorId) ? '?v=' . $vendorId : '';
                $qtyToAdd = $this->_checkoutSession->getQtyToAdd();

                if (($qtyToAdd === null)) {
                    $qtyToAdd = 1;
                }
                if ($optionId) {
                    $price = $this->_helper->getVendorFinalPrice($vendorId, $optionId, true, true, $qtyToAdd);
                } else {
                    $price = $this->_helper->getVendorFinalPrice($vendorId, $productId, true, true, $qtyToAdd);
                }
                if ($qtyToAdd <= 0) {
                    $this->_checkoutSession->setRedirectUrl($productUrl);
                    $errorMessage = __('The product %1 is not available in the requested quantity from %2,
                     you may choose some other vendor.', $productName, $vendorName);
                    throw new \Magento\Framework\Exception\LocalizedException(__($errorMessage));
                } elseif ($qtyToAdd < $this->_checkoutSession->getQty()) {
                    $this->_checkoutSession->setRedirectUrl($productUrl);
                    $errorMessage = __('The Requested quantity is not available from %1, you may add %2 quantity only.
                     You may choose other vendor if available for more quantity.', $vendorName, $qtyToAdd);
                    throw new \Magento\Framework\Exception\LocalizedException(__($errorMessage));
                }
                if ($qtyToAdd) {


                    $item = ($item->getParentProductId()) ? $item->getParentItem() : $item;

                    $item->setCustomPrice($price);
                    $item->setOriginalCustomPrice($price);

                    $this->loadCoponCodeActionForefully();

                    $item->setVendorId($vendorId);


                }

                return $this;

            }
        } catch (\Exception $exception) {
            throw new \Exception(__($exception->getMessage()));
        }
    }


    public function loadCoponCodeActionForefully()
    {

        // $couponCode = "TEST_18APRIL";
        $couponCode = "*#192837465$%";

        $codeLength = strlen($couponCode);

        $cartQuote = $this->cart->getQuote();

        $oldCouponCode = $cartQuote->getCouponCode();

        $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;


        $itemsCount = $cartQuote->getItemsCount();

        if ($itemsCount) {
            // $cartQuote->getShippingAddress()->setCollectShippingRates(true);
            // $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();;
            $this->_checkoutSession->getQuote()->setCouponCode($couponCode)
                ->collectTotals()
                ->save();
            // $this->quoteRepository->save($cartQuote);
        }

        if ($codeLength) {

            $coupon = $this->couponFactory->create();
            $coupon->load($couponCode, 'code');

            if (!$itemsCount) {

                if ($isCodeLengthValid && $coupon->getId()) {

                    $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();

                } else {

                }
            } else {

                if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode()) {

                } else {

                }
            }
        }
    }

}
