<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Controller\Sellerhtml\Product;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Product Select Category
 */
class Import extends \Magedelight\Backend\App\Action
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
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->design = $design;
        parent::__construct($context);
    }

    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->design->applyVendorDesign();
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Select Category'));
        return $resultPage;
    }
}
