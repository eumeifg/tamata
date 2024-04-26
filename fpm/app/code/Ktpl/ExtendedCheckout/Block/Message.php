<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ktpl\ExtendedCheckout\Block;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use MDC\Catalog\Model\Product;

/**
 * Totals cart block.
 *
 * @api
 * @since 100.0.2
 */
class Message extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @var array
     */
    protected $_totalRenderers;

    /**
     * @var string
     */
    protected $_defaultRenderer = \Magento\Checkout\Block\Total\DefaultTotal::class;

    /**
     * @var array
     */
    protected $_totals = null;

    /**
     * @var Data
     */
    protected $_priceData;

    /**
     * @var Data
     */
    protected $_specialPriceData;

    /**
     * @var Data
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        Product $specialPriceData,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);        
        $this->_specialPriceData = $specialPriceData;
        $this->_customerSession = $customerSession;
    }

    /**
     * Get active or custom quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote();
        }

        if (null === $this->_quote) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Get simple product special price
     *
     */
    public function getSimpleProductVendorPriceOnCart($vendorId, $productId)
    {
        $vendorItemData = $this->_specialPriceData->getVendorProduct($vendorId, $productId);
        return $vendorItemData;
    }

    /**
     * To check  user is logged in or not
     *
     */
    public function checkIsLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }
}
