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
namespace Magedelight\Catalog\Observer\Cart;

use Magento\Framework\Event\ObserverInterface;

class BeforeAddProductToCart implements ObserverInterface
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\Add $subject
     * @return type
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $requestInfo = $event->getInfo();
        if (is_array($requestInfo)) {
            $requestInfo = new \Magento\Framework\DataObject($requestInfo);
        }
        $simpleProductId = $requestInfo->getData('simple_product');

        if ($simpleProductId) {
            $this->checkoutSession->setOptionId($simpleProductId);
        } else {
            $this->checkoutSession->setOptionId(null);
        }
    }
}
