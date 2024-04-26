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
namespace Magedelight\Vendor\Model;

class Form extends \Magento\Eav\Model\Form
{
    /**
     * XML configuration paths for "Disable autocomplete on storefront" property
     */
    const XML_PATH_ENABLE_AUTOCOMPLETE = 'general/restriction/autocomplete_on_storefront';

    /**
     * Current module pathname
     *
     * @var string
     */
    protected $_moduleName = 'Magedelight_Vendor';

    /**
     * Current EAV entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = 'vendor';

    /**
     * Get EAV Entity Form Attribute Collection for Vendor
     * exclude 'created_at'
     *
     * @return \Magedelight\Vendor\Model\ResourceModel\Form\Attribute\Collection
     */
    protected function _getFormAttributeCollection()
    {
        return parent::_getFormAttributeCollection()->addFieldToFilter('attribute_code', ['neq' => 'created_at']);
    }
}
