<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OffersImportExport
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OffersImportExport\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Offers extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->context = $context;
        parent::__construct($context);
    }
    
     /**
      * @return bool
      */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Magedelight_OffersImportExport::vendor_offers');
    }
}
