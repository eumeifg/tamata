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
namespace Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Vendor;

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

    protected $_orderFactory;
    
    protected $_orderItemCollectionFactory;
    
    /**
     * @var \Magedelight\Sales\Model\Order
     */
    protected $vendorOrder;

    /**
     * @var \Magedelight\Vendor\Model\VendorRepository
     */
    protected $vendorRepository;

    protected $_messageHelper;
    
    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Vendor\Model\VendorRepository $vendorRepository
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Vendor\Model\VendorRepository $vendorRepository,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Config $salesConfig,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        array $data = []
    ) {
        $this->vendorRepository = $vendorRepository;
        $this->vendorOrder = $vendorOrderFactory->create();
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context, $registry, $data);
        
        $this->salesConfig = $salesConfig;
        $this->_messageHelper = $messageHelper;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    
    public function getOrder()
    {
        //$orderId = $this->_coreRegistry->registry('vendor_order')->getOrderId();
        //$order = $this->_orderFactory->create()->load($orderId);
        $order = $this->_coreRegistry->registry('vendor_order')->getOriginalOrder();
        return $order;
    }
    
    /**
     *
     * @param int $vendorId
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getVendor($vendorId)
    {
        return $this->vendorRepository->getById($vendorId);
    }
    
    /**
     *
     * @param int $orderId
     * @param int $vendorId
     * @return \Magedelight\Sales\Model\Order
     */
    public function getVendorOrder($orderId, $vendorId)
    {
        return $this->vendorOrder->getByOriginOrderId($orderId, $vendorId);
    }

    public function getGiftOptionsMessage($giftMessageId = '')
    {
        if ($giftMessageId) {
            $message = $this->_messageHelper->getGiftMessage(
                $giftMessageId
            );
            return $message;
        }
    }
    
    /**
     * @param array $filterByTypes
     * @param bool $nonChildrenOnly
     * @return ImportCollection
     */
    public function getItemsCollection()
    {
       // $vendorOrder = $this->getVendorOrder();
        $vendorOrder = $this->_coreRegistry->registry('vendor_order');
        
        return $vendorOrder->getItems();
    }
    
    public function getOrderStatusLabel($statusCode)
    {
        return $this->salesConfig->getStatusLabel($statusCode);
    }
}
