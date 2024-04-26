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
namespace Magedelight\Sales\Block\Adminhtml\Sales\Order\View;

use Magento\Sales\Model\Order\Address;
use Magento\Store\Model\ScopeInterface;

/**
 * Order history block
 * Class Info
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
{
    /**
     * @var \Magedelight\Sales\Model\Order
     */
    protected $vendorOrder;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadata
     * @param \Magento\Customer\Model\Metadata\ElementFactory $elementFactory
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Customer\Model\Metadata\ElementFactory $elementFactory,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        Address\Renderer $addressRenderer,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        array $data = []
    ) {
        $this->vendorOrder = $vendorOrderFactory->create();
        $this->vendorHelper = $vendorHelper;
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct(
            $context,
            $registry,
            $adminHelper,
            $groupRepository,
            $metadata,
            $elementFactory,
            $addressRenderer,
            $data
        );
    }

    /**
     * @return $this
     */
    public function getVendorOrder()
    {
        $vendorOrderId = $this->getRequest()->getParam('vendor_order_id');

        $vendorId = $this->getRequest()->getParam('do_as_vendor', false);
        if (!$vendorId && $this->getRequest()->getParam('invoice_id', false)) {
            $vendorOrderId = $this->getParentBlock()->getInvoice()->getVendorOrderId();
        } elseif (!$vendorId && $this->getRequest()->getParam('shipment_id', false)) {
            $vendorOrderId = $this->getParentBlock()->getShipment()->getVendorOrderId();
        } elseif (!$vendorId && $this->getRequest()->getParam('creditmemo_id', false)) {
            $vendorId = $this->getParentBlock()->getCreditmemo()->getVendorId();
        }
        return ($vendorOrderId) ? $this->vendorOrderRepository->getById($vendorOrderId) : null;
    }

    public function getVendorDetail($vendorId)
    {
        return $this->vendorHelper->getVendorDetails($vendorId);
    }

    public function getVendorGstin($vendorId)
    {
        $vendor = $this->getVendorDetail($vendorId);
        if ($this->vendorHelper->isModuleEnabled('RB_VendorInd') &&
            $this->vendorHelper->getConfigValue('rbvendorind/general_settings/enabled')) {
            if (!empty($vendor)) {
                return $vendor->getGstin();
            }
        }
    }

    public function getFullAddress($vendorId)
    {
        $vendor = $this->getVendorDetail($vendorId);
        if (!empty($vendor)) {
            return $vendor->getAddress1() . ' ' . $vendor->getAddress2() . ' '
                . $vendor->getCity() . ' ' . $vendor->getRegion() . ' ' . $vendor->getPincode();
        }
    }

    /**
     *
     * @return bool
     */
    public function isMagentoOrderStatusDisplayed()
    {
        return $this->_scopeConfig->getValue(
            \Magedelight\Sales\Model\Order::IS_MAGENTO_ORDER_STATUS_ALLOWED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
