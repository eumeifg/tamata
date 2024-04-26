<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Product List Sortable allowed sortable attributes source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magedelight\CatalogGraphQl\Model\Config\Source;

class ListSort extends \Magento\Catalog\Model\Config\Source\ListSort
{
    /**
     * Retrieve option values array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        $options['most_viewed'] = __('Popularity');
        $options['random'] = __('Random');
        return $options;
    }
}
