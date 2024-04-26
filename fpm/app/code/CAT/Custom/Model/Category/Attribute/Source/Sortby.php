<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CAT\Custom\Model\Category\Attribute\Source;

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
            $this->_options = [['label' => __('Position'), 'value' => 'position'],['label' => __('Random'), 'value' => 'random']];
            foreach ($this->_getCatalogConfig()->getAttributesUsedForSortBy() as $attribute) {
                $this->_options[] = [
                    'label' => __($attribute['frontend_label']),
                    'value' => $attribute['attribute_code']
                ];
            }
        }
        return $this->_options;
    }
}
