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
use Magedelight\Vendor\Api\AccountManagementInterface;

/**
 * Description of ContactPost
 *
 * @author Rocket Bazaar Core Team
 */
class ContactPost extends \Magedelight\Backend\App\Action
{
    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     *
     * @param Context $context
     * @param AccountManagementInterface $accountManagement
     * @return type
     */
    public function __construct(
        Context $context,
        AccountManagementInterface $accountManagement
    ) {
        $this->accountManagement = $accountManagement;
        return parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->_redirect('rbvendor');
            return;
        }
        $vendor = $this->_auth->getUser();
        if (empty($post['query']) || empty($post['message'])) {
            $this->messageManager->addWarning(
                __("Please enter query and query description.")
            );
            $this->_redirect('*/*/contact');
            return;
        }
        try {
            $this->accountManagement->submitVendorQuery($vendor, $post);
            $this->messageManager->addSuccess(
                __("Thanks for contacting us with your comments and questions. We'll respond to you very soon.")
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));
        }
        $this->_redirect('*/*/contact');
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::account');
    }
}
