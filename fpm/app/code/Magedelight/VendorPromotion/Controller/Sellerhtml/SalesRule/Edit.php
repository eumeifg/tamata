<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Controller\Sellerhtml\SalesRule;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends AbstractAction
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @param Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     */
    public function __construct(
        Context $context,
        \Magedelight\VendorPromotion\Helper\Data $helper,
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory
    ) {
        $this->design = $design;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context, $helper);
    }
    
    public function execute()
    { 
        $this->design->applyVendorDesign();
        if ($this->getRequest()->getParam('id', false)) {
            $this->validateRule();
        }
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
    
    public function validateRule()
    {
        $ruleId = $this->getRequest()->getParam('id');
        $vendorId = $this->_auth->getUser()->getVendorId();
        $collection = $this->ruleFactory->create()->getCollection()
            ->addFieldToFilter('rule_id', $ruleId)
            ->addFieldToFilter('vendor_id', ['finset' => $vendorId]);
        $collection->load();
        if (!$collection->getFirstItem()->getId()) {
            $this->messageManager->addError(__('Rule Not Found'));
            $this->_redirect('*/*');
            return;
        }
        return $this;
    }
}
