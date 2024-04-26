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
namespace Magedelight\Commissions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Vendorcommission extends AbstractDb
{
    /**
     * @param Context $context
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        ManagerInterface $eventManager
    ) {
        $this->eventManager     = $eventManager;
        parent::__construct($context);
    }
    
    protected function _construct()
    {
        $this->_init('md_vendor_commissions', 'vendor_commission_id');
    }
}
