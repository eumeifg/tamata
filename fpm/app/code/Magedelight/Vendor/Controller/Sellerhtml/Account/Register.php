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

use Magedelight\Vendor\Model\Registration;

/**
 * Vendor sign-up controller
 * @author Rocket Bazaar Core Team
 *  Created at 24 Feb, 2016 5:17:07 PM
 */
class Register extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;
    
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var \Magedelight\Vendor\Model\VendorRepository
     */
    protected $vendorRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Registration
     */
    protected $registration;
    
    /**
     * @var \Magedelight\Vendor\Api\AccountManagementInterface
     */
    protected $accountManagement;
    
    /*
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Vendor\Model\VendorRepository $vendorRepository,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magedelight\Vendor\Model\Design $design,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        Registration $registration,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magedelight\Vendor\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->registry = $registry;
        $this->vendorRepository = $vendorRepository;
        $this->design = $design;
        $this->pageFactory = $pageFactory;
        $this->registration = $registration;
        $this->urlModel = $urlFactory->create();
        $this->vendorFactory = $vendorFactory;
        $this->accountManagement = $accountManagement;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $result = $this->accountManagement->registerVendor();
        $response = $this->jsonHelper->jsonDecode($result);
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if (is_array($response) && array_key_exists('message', $response)) {
            if ($response['message']['type'] == 'notice') {
                $this->messageManager->addNoticeMessage($response['message']['data']);
                return $resultRedirect->setPath($response['redirect_url']);
            } elseif ($response['message']['type'] == 'error') {
                $this->messageManager->addErrorMessage($response['message']['data']);
                return $resultRedirect->setPath($response['redirect_url']);
            } elseif ($response['message']['type'] == 'success') {
                $this->messageManager->addSuccessMessage($response['message']['data']);
                return $resultRedirect->setPath($response['redirect_url']);
            }
        } elseif (isset($response['redirect_url'])) {
            return $resultRedirect->setPath($response['redirect_url']);
        }
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Create New Vendor Account'));
        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return true;
    }
}
