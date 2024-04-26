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
namespace Magedelight\Sales\Controller\Sellerhtml\Order;

use Magedelight\Backend\App\Action\Context;
use Magedelight\Sales\Model\Order;

/**
 * @author Rocket Bazaar Core Team
 */
class Intransit extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magedelight\Sales\Model\OrderFactory
     */
    protected $_vendorOrder;

    /**
     *
     * @param Context $context
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrder
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Magedelight\Sales\Model\OrderFactory $vendorOrder,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_vendorOrder = $vendorOrder->create();
        $this->_logger = $logger;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $vendorId = $this->_auth->getUser()->getVendorId();
        $id = $this->getRequest()->getParam('id');
        $vendorOrder = $this->_vendorOrder->load($id);
        if ($vendorOrder->getId()) {
            try {
                $vendorOrder->setData("status", Order::STATUS_IN_TRANSIT)
                    ->save();
                $this->messageManager->addSuccess(
                    __('The order status changed to in transit.')
                );
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $this->messageManager->addError(__('The order status cannot be changed to in transit'));
            }
        }
        $this->_redirect('rbsales/order/index/sfrm/intransit/', ['tab' => $this->getTab()]);
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
