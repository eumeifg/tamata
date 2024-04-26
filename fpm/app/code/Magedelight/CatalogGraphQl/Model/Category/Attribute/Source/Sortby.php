<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\CatalogGraphQl\Model\Category\Attribute\Source;

/**
 * Catalog Category *_sort_by Attributes Source Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Sortby extends \Magento\Catalog\Model\Category\Attribute\Source\Sortby
{
    /**
     * @inheritdoc
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            parent::getAllOptions();
            $this->_options[] = ['label' => __('Popularity'), 'value' => 'most_viewed'];
            $this->_options[] = ['label' => __('Random'), 'value' => 'random'];
        }
        return $this->_options;
    }
}
