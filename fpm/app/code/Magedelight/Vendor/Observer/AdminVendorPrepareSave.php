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
namespace Magedelight\Vendor\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Description of Register
 *
 * @author Rocket Bazaar Core Team
 */
class AdminVendorPrepareSave implements ObserverInterface
{

    /**
     * @var \Magedelight\Vendor\Model\VendorRepository
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Vendor\Model\VendorWebsiteRepository
     */
    protected $vendorWebsiteRepository;

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_status_change/template';

    /**
     * AdminVendorPrepareSave constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magedelight\Vendor\Model\VendorWebsiteRepository $vendorWebsiteRepository
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\Vendor\Model\VendorWebsiteRepository $vendorWebsiteRepository
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->date = $date;
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $oldStatus = '';
        if ($observer->getEvent()->getAction()) {
            $status = $observer->getEvent()->getStatus();
        } else {
            $status = $observer->getEvent()->getVendor()->getStatus();
            $oldStatus = $observer->getEvent()->getOldStatus();
        }

        $isRegistrationNew = $observer->getEvent()->getIsRegnNew();

        $email = $observer->getEvent()->getVendor()->getEmail();
        $vendor = $this->vendorWebsiteRepository->getVendorWebsiteData(
            $observer->getEvent()->getVendor()->getVendorId(),
            $observer->getEvent()->getVendor()->getWebsiteId()
        );
        $storeId = $vendor->getData('store_id');
        $vacationFrom = $vendor->getVacationFromDate();
        $vacationTo = $vendor->getVacationToDate();
        $vacationDateRange = '';
        if ($vacationFrom && $vacationTo) {
            $vacationDateRange = " (From $vacationFrom To $vacationTo)";
        }
        $reason = ($observer->getEvent()->getVendor()->getVacationMessage() && $status == 4) ?
            $observer->getEvent()->getVendor()->getVacationMessage() . $vacationDateRange :
            $vendor->getStatusDescription();

        if ($status == 0) {
            $stLabel = 'Pending';
        } elseif ($status == 1) {
            $stLabel = 'Active';
        } elseif ($status == 2) {
            $stLabel = 'Inactive';
        } elseif ($status == 3) {
            $stLabel = 'Disapproved';
        } elseif ($status == 4) {
            $stLabel = 'On Vacation';
        } else {
            $stLabel = 'Closed';
        }

        if ($vendor && $vendor->getId() && !$isRegistrationNew &&
            ($status != $oldStatus) || $observer->getEvent()->getAction()) {
            /* Send mail if status changed for existing record of vendor. */
            $this->_sendNotification(
                $email,
                $stLabel,
                $vendor->getBusinessName(),
                $reason,
                $storeId,
                $vacationFrom,
                $vacationTo
            );
        }
    }

    /**
     * @param $email
     * @param $stLabel
     * @param $displayname
     * @param $reason
     * @param int $storeId
     * @param $vacationFrom
     * @param $vacationTo
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotification(
        $email,
        $stLabel,
        $displayname,
        $reason,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        $vacationFrom,
        $vacationTo
    ) {
        $templateVars = [];
        if ($stLabel == 'Active' || $stLabel == 'Closed' || $stLabel == 'Pending') {
            $templateVars = [
                'status' => $stLabel,
                'disply_name'  => $displayname
            ];
        } else {
            $templateVars = [
                'status' => $stLabel,
                'disply_name'  => $displayname,
                'reason'  => $reason
            ];
        }

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => $storeId,
                ]
            )
            ->setFromByScope('general')
            ->setTemplateVars($templateVars)
            ->addTo($email)
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }
}
