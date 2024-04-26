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
namespace Magedelight\Vendor\Controller\Sellerhtml\Review\Customer;

class Viewfeedback extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    private $vendorDesign;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\Design $vendorDesign
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\Design $vendorDesign
    ) {
        parent::__construct($context);
        $this->vendorDesign = $vendorDesign;
    }
    
    public function execute()
    {
        $this->vendorDesign->applyVendorDesign();
        $reviewId = $this->getRequest()->getParam('review_id');
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::services');
    }
}
