<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
/**
 * @author Rocket Bazaar Core Team
 *  Created at 28 Jun, 2016 11:50:58 AM
 */
namespace Magedelight\Catalog\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

class CheckoutCartAddObserver implements ObserverInterface
{
    protected $_layout;
    protected $_request;
    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $_vendorHelper;

    protected $serializer;

    /**
     * CheckoutCartAddObserver constructor.
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        Json $serializer = null
    ) {
        $this->_layout = $layout;
        $this->_request = $request;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->_vendorHelper = $vendorHelper;
    }

    /**
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $item = $observer->getQuoteItem();
        $additionalOptions = $item->getOptionByCode('additional_options');
        $vendorId = (int)$item->getVendorId();

        if ($vendorId) {
            if ((!$additionalOptions)) {
                $soldBy = $this->_vendorHelper->getVendorNameById($vendorId);
                $additionalOptions[] =  ['code'  => 'vendor',
                                            'label' => __('Sold By'),
                                            'value' => $soldBy
                                        ];

                $item->addOption(['product_id' => $item->getProductId(),
                                       'code' => 'additional_options',
                                       'value' => $this->serializer->serialize($additionalOptions)]);
            }
        }
    }
}
