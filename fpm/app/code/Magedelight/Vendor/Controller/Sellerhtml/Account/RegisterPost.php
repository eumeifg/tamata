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

use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magedelight\Backend\App\Action\Context;
use Magedelight\Vendor\Model\Registration;
use Magedelight\Vendor\Model\Upload;
use Magedelight\Vendor\Model\Vendor\File as FileModel;
use Magedelight\Vendor\Model\Vendor\Image as ImageModel;
use Magedelight\Vendor\Model\VendorRepository;

/**
 * @author Rocket Bazaar Core Team
 *  Created at 13 Feb, 2016 12:04:25 PM
 */
class RegisterPost extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Vendor\Api\AccountManagementInterface
     */
    protected $accountManagement;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param \Magedelight\Vendor\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @return type
     */
    public function __construct(
        Context $context,
        \Magedelight\Vendor\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->accountManagement = $accountManagement;
        $this->jsonHelper = $jsonHelper;
        return parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->accountManagement->registerPostVendor();
        $response = $this->jsonHelper->jsonDecode($result);
        
        if (is_array($response) && array_key_exists('message', $response)) {
            if ($response['message']['type'] == 'notice') {
                if (is_array($response['message']['data'])) {
                    $this->messageManager->addSuccessMessage(__('Something went wrong, please try again.'));
                } else {
                    $this->messageManager->addNoticeMessage($response['message']['data']);
                }
            } elseif ($response['message']['type'] == 'error') {
                if (is_array($response['message']['data'])) {
                    $this->messageManager->addErrorMessage(implode(', ', $response['message']['data']));
                } else {
                    $this->messageManager->addErrorMessage($response['message']['data']);
                }
            } elseif ($response['message']['type'] == 'warning') {
                if (is_array($response['message']['data'])) {
                    $this->messageManager->addSuccessMessage(__('Something went wrong, please try again.'));
                } else {
                    $this->messageManager->addErrorMessage($response['message']['data']);
                }
            } elseif ($response['message']['type'] == 'success') {
                $this->messageManager->addSuccessMessage($response['message']['data']);
            }
        }
        $url = $response['redirect_url'];
        $this->_redirect($this->getRedirectUrl($response));
    }
    
    /**
     * Created to achieve cusomizations.
     * @param array $response
     * @return string
     */
    public function getRedirectUrl($response = [])
    {
        return $response['redirect_url'];
    }
    
    /**
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
