<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
namespace Magedelight\Wishlist\Model\Item\Plugin;

use Magento\Catalog\Model\Product\Type;

/**
 * Description of AfterGetModel
 *
 * @author Rocket Bazaar Core Team
 */
class AfterGetModel
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * AfterGetModel constructor.
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
    }

    /**
     * Retrieve item product instance
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Model\Product
     */
    public function afterGetProduct(
        \Magento\Wishlist\Model\Item $subject,
        $result
    ) {
        $vendorId = $subject->getVendorId();
        $productId = $result->getId();
        if ($vendorId) {
            $this->checkoutSession->setProductVendorId($vendorId);
            
            if ($result->getTypeId() == Type::TYPE_SIMPLE) {
                $price = $this->helper->getVendorFinalPrice($vendorId, $productId);
                $result->setPrice($price);
                $result->setVendorId($vendorId);
            }
        }
        return $result;
    }

    /**
     * Retrieve item product instance
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Model\Product
     */
    public function beforeGetProduct(
        \Magento\Wishlist\Model\Item $subject
    ) {
        if ($subject->getVendorId()) {
            $this->request->setParam('v', $subject->getVendorId());
        }
    }
}
