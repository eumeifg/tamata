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
namespace Magedelight\Sales\Plugin;

use Magedelight\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\Data\InvoiceExtensionInterface;
use Magento\Sales\Api\Data\InvoiceInterface;

class AddVendorInInvoice
{
    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var InvoiceExtensionFactory
     */
    protected $extensionFactory;

    /**
     *
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param InvoiceExtensionFactory $extensionFactory
     * @param \Magedelight\Sales\Api\Data\OrderDataInterfaceFactory $orderDataInterface
     */
    public function __construct(
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        InvoiceExtensionFactory $extensionFactory,
        \Magedelight\Sales\Api\Data\OrderDataInterfaceFactory $orderDataInterface
    ) {
        $this->vendorRepository = $vendorRepository;
        $this->extensionFactory = $extensionFactory;
        $this->orderDataInterface = $orderDataInterface;
    }

    /**
     * @param InvoiceInterface $entity
     * @param InvoiceExtensionInterface|null $extension
     * @return \Magento\Sales\Api\Data\InvoiceExtension|InvoiceExtensionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetExtensionAttributes(
        InvoiceInterface $entity,
        InvoiceExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }
        $vendorDetails =  $this->vendorRepository->getById($entity->getVendorId());
        $address2 = (!($vendorDetails->getAddress2() === null)) ? ', ' . $vendorDetails->getAddress2() : '';
        $vendorAddress = $vendorDetails->getAddress1() . $address2;
        $orderData = $this->orderDataInterface->create();
        $orderData->setVendorId($vendorDetails->getVendorId());
        $orderData->setVendorName($vendorDetails->getBusinessName());
        $orderData->setVat($vendorDetails->getVat());
        $orderData->setAddress($vendorAddress);
        $extension->setVendorData($orderData);
        $entity->setExtensionAttributes($extension);
        return $extension;
    }
}
