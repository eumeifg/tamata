<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Controller\Adminhtml;

abstract class Vendorcommission extends \Magento\Backend\App\Action
{

    protected $_vendorcommissionFactory;
    
    protected $resultPageFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Commissions\Model\VendorcommissionFactory $vendorcommissionFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_vendorcommissionFactory = $vendorcommissionFactory;
        $this->resultPageFactory = $resultPageFactory;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::manage');
    }
}
