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


class Index extends AbstractAction
{
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    private $design;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magedelight\VendorPromotion\Helper\Data $helper,
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->design = $design;
        parent::__construct($context,$helper);
    }
        
    public function execute()
    {
        $this->design->applyVendorDesign();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Cart Price Rules'));
        return $resultPage;
    }

}
