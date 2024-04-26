<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Catalog\Model\Product\Request;

class Reject
{

    /**
     * @var \Magento\Framework\Event\Manager
     */
    private $eventManager;

    /**
     *
     * @param \Magento\Framework\Event\Manager $eventManager
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * @param integer $requestId
     * @param array $postData
     */
    public function execute($requestId, $postData = [])
    {
        $eventParams = ['product_status' => 'disapproved','id' => $requestId, 'post_data' => $postData];
        $this->eventManager->dispatch('vendor_product_admin_status_change', $eventParams);
    }
}
