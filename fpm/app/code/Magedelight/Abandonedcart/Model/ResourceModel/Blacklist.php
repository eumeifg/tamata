<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Model\ResourceModel;

use Magedelight\Abandonedcart\Model\Blacklist as BlacklistModel;
use Magento\Framework\Model\AbstractModel;

class Blacklist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    /**
     * Initialize resources
     *
     * @return void
     */
    protected $date;
    
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->date = $date;
        parent::__construct($context);
    }
    
    protected function _construct()
    {
        $this->_init('md_abandonedcart_email_black_list', 'black_list_id');
    }
}
