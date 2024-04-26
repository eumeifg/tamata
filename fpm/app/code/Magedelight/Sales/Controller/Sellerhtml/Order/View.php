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
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;

class View extends \Magedelight\Backend\App\Action implements OrderLoaderInterface
{

    /**
     * @var \Magedelight\Sales\Model\Order
     */
    private $vendorOrder;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;
    
    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $resultPageFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->design = $design;
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->url = $context->getUrl();
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->vendorOrder = $vendorOrderFactory->create();
        parent::__construct($context);
    }
    
    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->load($this->_request)) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addError(__('Specified order does not found.'));
            $resultRedirect->setPath('*/*/index', ['tab'=> $this->getTab()]);
            return $resultRedirect;
        }
        $this->design->applyVendorDesign();
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    public function load(RequestInterface $request)
    {
        $id = (int) $request->getParam('id');
        if (!$id) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        if ($this->getVendorOrder()) {
            return true;
        }
        return false;
    }

    protected function getVendorOrder()
    {
        if (!($vendorId = $this->_auth->getUser()->getVendorId())) {
            return false;
        }
        $id = $this->getRequest()->getParam('id', false);

        $vendorOrder = $this->vendorOrder->load($id);
        if ($vendorOrder->getId() && $vendorOrder->getVendorId() == $vendorId) {
            $this->registry->register('vendor_order', $vendorOrder);
            return $vendorOrder;
        }
        return false;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
