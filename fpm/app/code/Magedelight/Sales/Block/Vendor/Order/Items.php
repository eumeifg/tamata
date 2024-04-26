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
namespace Magedelight\Sales\Block\Vendor\Order;

/**
 * customer order items rendered vendor wise.
 * @author Rocket Bazaar Core Team
 * Created at 28 May, 2016 02:51:11 PM
 */
class Items extends \Magento\Sales\Block\Order\Items
{

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $salesConfig;

    /**
     * @var \Magedelight\Vendor\Model\VendorRepository
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Items constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Vendor\Model\VendorRepository $vendorRepository
     * @param \Magedelight\Vendor\Model\VendorratingFactory $vendorrating
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Order\Config $salesConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Vendor\Model\VendorRepository $vendorRepository,
        \Magedelight\Vendor\Model\VendorratingFactory $vendorrating,
        \Magedelight\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Config $salesConfig,
        array $data = []
    ) {
        $this->vendorRepository = $vendorRepository;
        $this->_vendorrating = $vendorrating;
        $this->salesConfig = $salesConfig;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $registry, $data);
    }
    /**
     *
     * @param int $vendorId
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getVendor($vendorId)
    {
        try {
            $vendor = $this->vendorRepository->getById($vendorId);
            return $vendor;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param int $orderId
     * @param int $vendorId
     * @return \Magedelight\Sales\Api\Data\VendorOrderInterface
     */
    public function getVendorOrder($vendorOrderId)
    {
        return $this->orderRepository->getById($vendorOrderId);
    }

    /**
     * @param array $filterByTypes
     * @param bool $nonChildrenOnly
     * @return ImportCollection
     */
    public function getItemsCollection()
    {
        $vendorOrder = $this->_coreRegistry->registry('vendor_order');
        return $vendorOrder->getItems();
    }

    /**
     * @param $statusCode
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderStatusLabel($statusCode)
    {
        return $this->salesConfig->getStatusLabel($statusCode);
    }

    /**
     * @return string
     */
    public function getAjaxReviewUrl()
    {
        return $this->getUrl('rbvendor/review_index/ajaxreview/');
    }

    /**
     * @return mixed
     */
    public function currentIncrementId()
    {
        $orderData = $this->_coreRegistry->registry('current_order');
        return $orderData->getIncrementId();
    }

    /**
     * @return mixed
     */
    public function currentOrderID()
    {
        $orderData = $this->_coreRegistry->registry('current_order');
        return $orderData->getId();
    }

    /**
     * @param $vendorOrder
     * @return mixed
     */
    public function checkIdExist($vendorOrder)
    {
        $collection = $this->_vendorrating->create()
             ->getCollection()->addFieldToFilter('vendor_order_id', $vendorOrder);
        return $collection->getFirstItem()->getData('vendor_order_id');
    }
}
