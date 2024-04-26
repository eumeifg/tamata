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

use Magento\Framework\Event\ObserverInterface;

class CheckUpdateAvailability implements ObserverInterface
{

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->_vendorProductFactory = $vendorProductFactory->create();
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->_helper = $helper;
    }

    /**
     * check update availability.
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magedelight\Catalog\Observer\CheckUpdateAvailability
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            $count = 0;
            $flag = true;
            $cart = $observer->getEvent()->getCart();
            $data = $observer->getEvent()->getInfo();
            $items = $cart->getItems();
            $itemStr = '';
            $qty = 0;
            foreach ($items as $item) {
                //associated simple product means skip loop;
                if ($item->getParentItemId()) {
                    continue;
                }

                foreach ($data->getData() as $itemId => $itemInfo) {
                    if ($itemId == $item->getId()) {
                        if (!empty($itemInfo['qty'])) {
                            $qty = $itemInfo['qty'];
                        }
                    }
                }

                $vendorId = $item->getVendorId();

                if ($item->getProductType() == 'configurable') {
                    $productId = $item->getOptionByCode('simple_product')->getProduct()->getId();
                } else {
                    $productId = $item->getProductId();
                }
                $availablequantity =  $this->getVendorQty($vendorId, $productId);
                $presentqty = $item->getQty();

                if ($availablequantity >= $qty) {
                    /* put this condition because all cart product quantity
                     updated with requested product quantity amount */
                    if ($itemId == $item->getId()) {
                        $item->setQty($qty);
                    }
                } else {
                    $item->setQty($presentqty);
                    $count++;
                    $itemStr = $item->getName();
                    $flag = false;
                }
            }
            if ($count && !$flag) {
                $this->_checkoutSession->setCartWasUpdated(true);
                $erroMsg = __("The requested quantity for '%1' is not available.", $itemStr);
                throw new \Magento\Framework\Exception\LocalizedException($erroMsg);
            } elseif ($flag) {
                $this->_checkoutSession->setCartWasUpdated(true);
                $this->_messageManager->addSuccess(__('The product was updated in your shopping cart.'));
            }
            return $this;
        }
    }

    /**
     *
     * @param type $vendorId
     * @param type $productId
     * @return int
     * @throws \Exception
     */
    public function getVendorQty($vendorId, $productId)
    {
        $vendorProduct = $this->_vendorProductFactory->getVendorProduct($vendorId, $productId);
        //$vendorProduct =  $this->_vendorProductFactory->getVendorQtyOnRequest($vendorId, $productId);

        if ($vendorProduct && (!($vendorProduct->getQty() === null))) {
            return $vendorProduct->getQty();
        }
        return 0;
    }
}
