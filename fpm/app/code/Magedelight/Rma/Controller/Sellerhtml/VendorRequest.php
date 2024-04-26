<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magedelight\Rma\Controller\Sellerhtml;
use Magedelight\Backend\App\Action;

abstract class VendorRequest extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;


    protected $authSession;

    /**
     * @param Magedelight/Backend/App/Action $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Vendor\Model\Design $design,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->design = $design;
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->authSession = $authSession;
    }

    /**
     * 
     * @param \Magento\Framework\App\RequestInterface $request
     * @return type
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $this->design->applyVendorDesign();
        return parent::dispatch($request);
    }
    
    
    /**
     * Check order view availability
     *
     * @param \Magento\Rma\Model\Rma|\Magento\Sales\Model\Order $item
     * @return bool
     */
    protected function _canViewOrder($item)
    {
        $vendorId = $this->authSession->getUser()->getVendorId();
        if ($item->getId() && $vendorId && $item->getVendorId() == $vendorId) {
            return true;
        }
        return false;
    }

    /**
     * Try to load valid rma by entity_id and register it
     *
     * @param int $entityId
     * @return bool
     */
    protected function _loadValidRma($entityId = null)
    {
        $entityId = $entityId ?: (int) $this->getRequest()->getParam('entity_id');
        if (!$entityId || !$this->_isEnabledOnFront()) {
            $this->_forward('noroute');
            return false;
        }
        
        /** @var $rma \Magento\Rma\Model\Rma */
        $rma = $this->_objectManager->create(\Magento\Rma\Model\Rma::class)->load($entityId);
        if ($this->_canViewOrder($rma)) {
            $this->_coreRegistry->register('vendor_current_rma', $rma);
            return true;
        } else {
            $this->_redirect('*/*/history');
        }
        return false;
    }

    /**
     * Checks whether RMA module is enabled in system config
     *
     * @return boolean
     */
    protected function _isEnabledOnFront()
    {
        /** @var $rmaHelper \Magento\Rma\Helper\Data */
        $rmaHelper = $this->_objectManager->get(\Magento\Rma\Helper\Data::class);
        return $rmaHelper->isEnabled();
    }
}
