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

use Magedelight\Vendor\Api\AccountManagementInterface;
use Magedelight\Backend\App\Action\Context;

/**
 * Description of EditPost
 * @author Rocket Bazaar Core Team
 */
class EditPost extends \Magedelight\Backend\App\Action
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;
    
    /**
     * @param Context $context
     * @param AccountManagementInterface $accountManagement
     * @return type
     */
    
    public function __construct(
        Context $context,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->accountManagement = $accountManagement;
        $this->jsonHelper = $jsonHelper;
        return parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->accountManagement->editVendor();
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $response = $this->jsonHelper->jsonDecode($result);
        if (array_key_exists('message', $response)) {
            if ($response['message']['type'] == 'notice') {
                $this->messageManager->addNoticeMessage($response['message']['data']);
            } elseif ($response['message']['type'] == 'error') {
                $this->messageManager->addErrorMessage($response['message']['data']);
            } elseif ($response['message']['type'] == 'success') {
                $this->messageManager->addSuccessMessage($response['message']['data']);
                
                if (array_key_exists('section', $response) && $response['section'] == 'login-info') {
                    /* Flash message not getting displated for login info section. */
                    return $resultRedirect->setPath($response['redirect_url']);
                }
            }
        }
        return $resultRedirect->setPath($response['redirect_url']);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::account');
    }
}
