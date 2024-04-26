<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Block\Widget\Button;

/**
 * @method string getButtonKey()
 * @method string getRegion()
 * @method string getName()
 * @method int getLevel()
 * @method int getSortOrder()
 * @method string getTitle()
 */
class Item extends \Magento\Framework\DataObject
{
    /**
     * Object delete flag
     *
     * @var bool
     */
    protected $_isDeleted = false;

    /**
     * Set _isDeleted flag value (if $isDeleted parameter is defined) and return current flag value
     *
     * @param boolean $isDeleted
     * @return bool
     */
    public function isDeleted($isDeleted = null)
    {
        $result = $this->_isDeleted;
        if ($isDeleted !== null) {
            $this->_isDeleted = $isDeleted;
        }
        return $result;
    }
}
