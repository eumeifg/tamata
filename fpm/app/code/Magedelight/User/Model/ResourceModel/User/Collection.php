<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Model\ResourceModel\User;

/**
 * Description of Collection
 *
 * @author Rocket Bazaar Core Team
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
        /**
         * @var string
         */
    protected $_idFieldName = 'user_id';
    
    protected function _construct()
    {
        $this->_init('Magedelight\User\Model\User', 'Magedelight\User\Model\ResourceModel\User');
    }
}
