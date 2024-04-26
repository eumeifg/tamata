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

class Save extends \Magedelight\Backend\App\Action
{
    const ORDER_RESOURCE = 'Magedelight_Sales::manage_orders';
    /**
     * @var \Magedelight\Sales\Model\OrderFactory
     */
    protected $_vendorOrderFactory;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
    ) {
        $this->_vendorOrderFactory = $vendorOrderFactory;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        $vendorOrderId = $data['order_id'] = $this->getRequest()->getParam('order_id', false);
        if ($vendorOrderId) {
            $vendorOrder =  $this->_vendorOrderFactory->create()->load($vendorOrderId);
            if (!$vendorOrder->getVendorOrderId()) {
                throw new Exception('No Such Order Found.');
            }
            $vendorOrder->setData('is_confirmed', 1);
            $vendorOrder->setData('confirmed_at', date('Y-m-d'));
        } else {
            throw new Exception('No data found.');
        }

        try {
            $vendorOrder->save();

            $this->messageManager->addSuccess(__('Order Status has been updated successfully.'));

            $resultRedirect->setPath('*/*/index', ['tab'=>'2,0']);
            //$url = $this->urlModel->getUrl('*/*/index', ['tab' => '2,0',]);
            //$resultRedirect->setUrl($this->_redirect->success($url));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while update order status.'));
        }
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ORDER_RESOURCE);
    }
}
