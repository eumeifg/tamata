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
class Delivered extends \Magedelight\Backend\App\Action
{
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
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_vendorOrder = $vendorOrder->create();
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Change order status for delivered.
     */
    public function execute()
    {
        $vendorId = $this->_auth->getUser()->getVendorId();
        $id = $this->getRequest()->getParam('id');
        $vendorOrder = $this->_vendorOrder->load($id);
        if ($vendorOrder->getId()) {
            try {
                $vendorOrder->setData("status", Order::STATUS_COMPLETE)
                    ->save();
                $this->messageManager->addSuccess(
                    __('The order status changed to delivered.')
                );
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $this->messageManager->addError(__('The order status cannot be changed to delivered.'));
            }
        }
        
        $tab = $this->getTab();
        if (!empty($tab)) {
            
            $tabArr = explode(',', $tab);
            if (array_key_exists(1, $tabArr)) {
                $tabArr[1] = $tabArr[1] + 2;
            }
            $tab = implode(',', $tabArr);
        }
        $this->_redirect('rbsales/order/complete', ['tab' => $tab]);
    }
    
    /**
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
