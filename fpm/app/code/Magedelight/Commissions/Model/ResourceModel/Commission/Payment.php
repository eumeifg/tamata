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
namespace Magedelight\Commissions\Model\ResourceModel\Commission;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Payment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('md_vendor_commission_payment', 'vendor_payment_id');
    }
}
