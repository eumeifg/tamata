<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Adminhtml\Review;

abstract class Vendorrating extends \Magento\Backend\App\Action
{

    protected $_vendorratingFactory;
    protected $resultPageFactory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\VendorratingFactory $vendorratingFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\VendorratingFactory $vendorratingFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_vendorratingFactory = $vendorratingFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::vendorrating');
    }
}
