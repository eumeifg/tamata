<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Model;

class Shippingmatrix extends \Magento\Framework\Model\AbstractModel
{

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'md_shipping_matrixrate';

    /**
     * @var string
     */
    protected $_cacheTag = 'md_shipping_matrixrate';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'md_shipping_matrixrate';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate');
    }
}
