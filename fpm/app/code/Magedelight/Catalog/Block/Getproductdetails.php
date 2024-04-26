<?php
/**
 * Copyright © 2019 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
namespace Magedelight\Catalog\Block;

class Getproductdetails extends \Magento\Catalog\Block\Product\View\abstractView
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->authSession = $authSession;
        parent::__construct($context, $arrayUtils, $data);
    }

    /**
     *
     * @return boolean
     */
    public function checkifIsVendorLoggedIn()
    {
        if ($this->authSession->isLoggedIn()) {
            return true;
        }
        return false;
    }
}
