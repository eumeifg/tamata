<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Magedelight\VendorPromotion\Controller\Sellerhtml\SalesRule;

use Magedelight\Backend\App\Action\Context;

/**
 * Description of AbstractAction
 *
 * @author Rocket Bazaar Core Team
 */
abstract class AbstractAction extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\VendorPromotion\Helper\Data
     */
    protected $helper;

    public function __construct(
        Context $context,
        \Magedelight\VendorPromotion\Helper\Data $helper
    ) {
  
        parent::__construct($context);
        $this->helper = $helper;
    }
    
    protected function _isAllowed()
    { 
        if(!$this->helper->isEnabled()){
            return false;
        }
        return $this->_authorization->isAllowed('Magedelight_Vendor::promotion');
    }
}
