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
namespace Magedelight\Catalog\Controller\Sellerhtml\Listing;

use Magedelight\Backend\App\Action\Context;

class Ajaxlive extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $vendorDesign;
    
    /**
     * @param Context $context
     * @param \Magedelight\Vendor\Model\Design $vendorDesign
     */
    public function __construct(
        Context $context,
        \Magedelight\Vendor\Model\Design $vendorDesign
    ) {
        parent::__construct($context);
        $this->vendorDesign = $vendorDesign;
    }

    public function execute()
    {
        $this->vendorDesign->applyVendorDesign();
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
    
    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
