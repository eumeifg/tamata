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
namespace Magedelight\Vendor\Controller\Adminhtml;

use Magedelight\Vendor\Api\AccountManagementInterface;
use Magedelight\Vendor\Api\Data\VendorInterfaceFactory;
use Magedelight\Vendor\Api\VendorRepositoryInterface;
use Magedelight\Vendor\Controller\RegistryConstants;
use Magento\Backend\App\Action;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\Error;

abstract class Index extends Action
{
    protected $_defaultVendorIndexersFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var FileModel
     */
    protected $fileModel;

    /**
     * @var Upload
     */
    protected $uploadModel;

    /**
     * @var ImageModel
     */
    protected $imageModel;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $vendorFactory = null;

    /** @var VendorRepositoryInterface */
    protected $_vendorRepository;

    /** @var  \Magedelight\Vendor\Helper\View */
    protected $_viewHelper;

    /**
     * @var AccountManagementInterface
     */
    protected $vendorAccountManagement;

    /**
     * @var VendorInterfaceFactory
     */
    protected $vendorDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    protected $vendorProduct;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        VendorRepositoryInterface $vendorRepository,
        AccountManagementInterface $vendorAccountManagement,
        VendorInterfaceFactory $vendorDataFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magedelight\Vendor\Helper\View $viewHelper,
        \Magedelight\Vendor\Model\Vendor\Image $imageModel,
        \Magedelight\Vendor\Model\Vendor\File $fileModel,
        \Magedelight\Vendor\Model\Upload $uploadModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\Catalog\Model\Product $vendorProduct
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->vendorFactory = $vendorFactory;
        $this->_vendorRepository = $vendorRepository;
        $this->vendorAccountManagement = $vendorAccountManagement;
        $this->vendorDataFactory = $vendorDataFactory;
        $this->layoutFactory = $layoutFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_viewHelper = $viewHelper;
        $this->imageModel = $imageModel;
        $this->fileModel = $fileModel;
        $this->uploadModel = $uploadModel;
        $this->vendorProduct = $vendorProduct;

        parent::__construct($context);

        $this->localeDate = $localeDate;
        $this->date = $date;
    }
    /**
     * Vendor initialization
     *
     * @return string vendor id
     */
    protected function initCurrentVendor()
    {
        $vendorId = (int)$this->getRequest()->getParam('vendor_id');

        if ($vendorId) {
            $this->_coreRegistry->register(RegistryConstants::CURRENT_VENDOR_ID, $vendorId);
        }

        return $vendorId;
    }

    /**
     * Prepare vendor default title
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    protected function prepareDefaultVendorTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__('Vendors'));
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::vendor_manage');
    }

    /**
     * Add errors messages to session.
     *
     * @param array|string $messages
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!$error instanceof Error) {
                $error = new Error($error);
            }
            $this->messageManager->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
    }

    /**
     * @return \Magedelight\Vendor\Model\Vendor
     */
    protected function _initVendor()
    {
        $vendorId = $this->initCurrentVendor();
        $vendorData = [];

        $vendor = null;
        $isExistingVendor = (bool) $vendorId;
        try {
            $vendor = $this->vendorFactory->create()->getCollection()
                ->_addWebsiteData(['*'], $this->getRequest()->getParam('website_id'))
                ->addFieldToFilter('vendor_id', $vendorId)->getFirstItem();
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(__($e->getMessage()));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while editing the vendor.'));
        }
        if (!$isExistingVendor) {
            $vendorData = $this->_getSession()->getVendorData();
            $vendor->setData($vendorData['vendor']);
        }
        $this->_coreRegistry->register('md_vendor', $vendor);
        return $vendor;
    }
}
