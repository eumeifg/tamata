<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Sellerhtml\Account;

use Magedelight\Backend\App\Action\Context;
use Magedelight\Backend\Model\Url as VendorUrl;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magedelight\Backend\App\Action
{

    /**
     * @var VendorUrl
     */
    protected $vendorUrl;

    /**
     *
     * @param Context $context
     * @param VendorUrl $vendorUrl
     */
    public function __construct(
        Context $context,
        VendorUrl $vendorUrl
    ) {
        $this->vendorUrl = $vendorUrl;
        $this->formKeyValidator = $context->getFormKeyValidator();
        parent::__construct($context);
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->_auth->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            return $this->getRedirect($this->vendorUrl->getStartupPageUrl());
        }
        return $this->getRedirect($this->vendorUrl->getStartupPageUrl());
    }
    
    protected function _isAllowed()
    {
        return true;
    }
}
