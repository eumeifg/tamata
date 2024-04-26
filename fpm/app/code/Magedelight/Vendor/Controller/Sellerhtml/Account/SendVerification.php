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

use Magedelight\Backend\Model\Url as VendorUrl;

/**
 * @author Rocket Bazaar Core Team
 *  Created at 13 Feb, 2016 12:04:25 PM
 */
class SendVerification extends \Magedelight\Backend\App\Action
{
    /**
     * @var VendorUrl
     */
    protected $vendorUrl;

    /**
     * @var \Magedelight\Vendor\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param VendorUrl $vendorUrl
     * @param \Magedelight\Vendor\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        VendorUrl $vendorUrl,
        \Magedelight\Vendor\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->accountManagement = $accountManagement;
        $this->jsonHelper = $jsonHelper;
        $this->vendorUrl = $vendorUrl;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->_auth->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            return $this->getRedirect($this->vendorUrl->getStartupPageUrl());
        }

        $result = $this->accountManagement->sendVerificationMail();
        $responce = $this->jsonHelper->jsonDecode($result);

        if ($responce['message']['type'] == 'notice') {
            $this->messageManager->addNoticeMessage($responce['message']['data']);
        } elseif ($responce['message']['type'] == 'warning') {
            $this->messageManager->addWarningMessage($responce['message']['data']);
        } elseif ($responce['message']['type'] == 'error') {
            if (array_key_exists('error', $responce['message'])) {
                foreach ($responce['message']['error'] as $error) {
                    $this->messageManager->addErrorMessage($error);
                }
            } else {
                $this->messageManager->addErrorMessage($responce['message']['data']);
            }
        } elseif ($responce['message']['type'] == 'success') {
            $this->messageManager->addSuccessMessage($responce['message']['data']);
        }
        $url = $responce['redirect_url'];
        $this->_redirect($url);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
