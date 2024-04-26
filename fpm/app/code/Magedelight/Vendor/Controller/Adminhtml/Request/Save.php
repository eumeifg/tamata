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
namespace Magedelight\Vendor\Controller\Adminhtml\Request;

use Magedelight\Vendor\Model\Source\RequestStatuses;
use Magedelight\Vendor\Model\Source\RequestTypes;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;

class Save extends \Magedelight\Vendor\Controller\Adminhtml\Request
{

    /**
     * @var \Magedelight\Vendor\Api\AccountManagementInterface
     */
    protected $vendorAccountManagement;

    /**
     * @var RequestTypes
     */
    protected $requestTypes;

    /**
     * @var \Magedelight\Vendor\Model\Source\Status
     */
    protected $vendorStatuses;

    /**
     * @var \Magedelight\Vendor\Model\RequestFactory
     */
    protected $_vendorRequest;

    /**
     * @var \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface
     */
    protected $vendorWebsiteRepository;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var RequestStatuses
     */
    protected $requestStatuses;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magedelight\Vendor\Api\AccountManagementInterface $vendorAccountManagement
     * @param RequestStatuses $requestStatuses
     * @param RequestTypes $requestTypes
     * @param VendorStatus $vendorStatuses
     * @param \Magedelight\Vendor\Model\RequestFactory $vendorRequest
     * @param \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Vendor\Api\AccountManagementInterface $vendorAccountManagement,
        RequestStatuses $requestStatuses,
        RequestTypes $requestTypes,
        \Magedelight\Vendor\Model\Source\Status $vendorStatuses,
        \Magedelight\Vendor\Model\RequestFactory $vendorRequest,
        \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        $this->_vendorRequest = $vendorRequest;
        $this->date = $date;
        $this->requestStatuses = $requestStatuses;
        $this->vendorRepository = $vendorRepository;
        $this->vendorStatuses = $vendorStatuses;
        $this->requestTypes = $requestTypes;
        $this->vendorAccountManagement = $vendorAccountManagement;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::edit_request');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        /**
         * 1. Request is edit from admin
         * 2. New Request from admin.
         */

        $data = $this->getRequest()->getPostValue();
        $requestId = $this->getRequest()->getParam('request_id');
        if ($requestId) {
            $vendorId = $data['vendor_id'];
            $vendorRequest = $this->_vendorRequest->create()->load($requestId);
        } else {
            $vendorId = $data['vendor_name'];
            $vendorRequest = $this->_vendorRequest->create();
        }

        $vendor = $this->vendorRepository->getById($vendorId);

        try {
            $data['vendor_id'] = $vendor->getVendorId();
            $vendorRequest->setData($data)->save();

            $vendor->setData('vacation_from_date', $this->date->gmtDate('Y-m-d', $data['vacation_from_date']));
            $vendor->setData('vacation_to_date', $this->date->gmtDate('Y-m-d', $data['vacation_to_date']));
            $this->vendorRepository->save($vendor);

            $vendorWebsite = $this->vendorWebsiteRepository->getVendorWebsiteData(
                $vendor->getId(),
                $vendor->getWebsiteId()
            );
            if ($vendorWebsite && $vendorWebsite->getId()) {
                $vendorWebsite->setData(
                    'vacation_from_date',
                    $this->date->gmtDate('Y-m-d', $data['vacation_from_date'])
                );
                $vendorWebsite->setData('vacation_to_date', $this->date->gmtDate('Y-m-d', $data['vacation_to_date']));
                $vendorWebsite->setData('vacation_request_status', $data['status']);

                if ($data['status'] == RequestStatuses::VENDOR_REQUEST_STATUS_APPROVED) {
                    if ($data['request_type'] == RequestTypes::VENDOR_REQUEST_TYPE_VACATION
                        && $vendor->getVacationFromDate() == date("Y-m-d")) {
                        $vendorWebsite->setStatus(VendorStatus::VENDOR_STATUS_VACATION_MODE);
                    }

                    if ($data['request_type'] == RequestTypes::VENDOR_REQUEST_TYPE_CLOSE) {
                        $vendorWebsite->setStatus(VendorStatus::VENDOR_STATUS_CLOSED);
                    }
                } elseif ($data['status'] == RequestStatuses::VENDOR_REQUEST_STATUS_REJECTED) {
                    if ($data['request_type'] == RequestTypes::VENDOR_REQUEST_TYPE_VACATION &&
                        $vendor->getStatus() == VendorStatus::VENDOR_STATUS_VACATION_MODE) {
                        $vendorWebsite->setStatus(VendorStatus::VENDOR_STATUS_ACTIVE);
                    }
                }
                $vendorWebsite->save();
            }
            $emailData = $this->prepareEmailData($vendor, $data);
            $this->vendorAccountManagement->sendEmailStatusRequestNotification($vendor, $emailData);

            $this->messageManager->addSuccess(__('You saved this vendor request status.'));
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $requestId, '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving record.') . $e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }

    protected function prepareEmailData($vendor, $data)
    {
        $templateData = [];
        $templateData['request_status'] = $this->requestStatuses->getOptionText($data['status'])->getText();
        $templateData['vendor_status'] = $this->vendorStatuses->getOptionText($vendor->getStatus())->getText();
        $templateData['request_type'] = $this->requestTypes->getOptionText($data['request_type'])->getText();
        return $templateData;
    }
}
