<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendInvoice implements ObserverInterface
{
    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/invoice_new/template';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    private $vendorRepository;

    /**
     * SendInvoice constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Vendor\Helper\Data $vendorHelper
    ) {
        $this->logger = $logger;
        $this->vendorRepository = $vendorRepository;
        $this->scopeConfig = $scopeConfig;
        $this->vendorHelper = $vendorHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vendor = $this->vendorRepository->getById($observer->getEvent()->getTransport()['invoice']->getVendorId());
        $vendorAddress = $this->getFullAddress($vendor->getId());
        $vendorGstin = $this->getVendorGstin($vendor->getId());

        $invoice = $observer->getEvent()->getTransport()['invoice'];
        $invoice->setData('vendor_name', $vendor->getBusinessName());
        $invoice->setData('vendor_vat', $vendor->getVat());
        $invoice->setData('vendor_address', $vendorAddress);
        $invoice->setData('vendor_gstin', $vendorGstin);
    }

    /**
     * @param $vendorId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVendorGstin($vendorId)
    {
        $vendor = $this->vendorRepository->getById($vendorId);
        if ($this->vendorHelper->isModuleEnabled('RB_VendorInd') &&
            $this->vendorHelper->getConfigValue('rbvendorind/general_settings/enabled')) {
            if (!empty($vendor)) {
                return $vendor->getGstin();
            }
        }
    }

    /**
     * @param $vendorId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFullAddress($vendorId)
    {
        $vendor = $this->vendorRepository->getById($vendorId);
        if (!empty($vendor)) {
            return $vendor->getAddress1() . ' ' . $vendor->getAddress2() . ' ' . $vendor->getCity() . ' '
                . $vendor->getRegion() . ' ' . $vendor->getPincode();
        }
    }
}
