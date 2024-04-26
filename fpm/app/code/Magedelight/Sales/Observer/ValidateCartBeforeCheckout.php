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

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ValidateCartBeforeCheckout implements ObserverInterface
{

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

    /**
     * ValidateCartBeforeCheckout constructor.
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param CustomerCart $cart
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        CustomerCart $cart,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
    ) {
        $this->vendorProductFactory = $vendorProductFactory;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->cart = $cart;
    }

    /**
     * Set Cart Item data for promocode condition for vendor specific
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $quote = $this->cart->getQuote();
        $controller = $observer->getControllerAction();
        $invalidItems = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            //associated simple product means skip loop;
            if ($item->getParentItemId()) {
                continue;
            }

            if ($item->getProductType() == Configurable::TYPE_CODE) {
                $productId = $item->getOptionByCode('simple_product')->getProduct()->getId();
            } else {
                $productId = $item->getProductId();
            }

            $availableQty =  $this->getVendorQty($item->getVendorId(), $productId);
            if ($item->getQty() > $availableQty) {
                $invalidItems[] = $item->getName();
            }
        }

        if (count($invalidItems) > 0) {
            $this->messageManager->addNoticeMessage(
                __(
                    "The requested quantity for item(s) [ '%1' ] is not available.
                     Please update your cart or try other item(s).",
                    implode(', ', $invalidItems)
                )
            );
            $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
        }
    }

    /**
     *
     * @param type $vendorId
     * @param type $productId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorQty($vendorId, $productId)
    {
        $vendorProduct = $this->vendorProductFactory->create()->getVendorProduct($vendorId, $productId);

        if ($vendorProduct && (!($vendorProduct->getQty() === null))) {
            return $vendorProduct->getQty();
        }
        return 0;
    }
}
