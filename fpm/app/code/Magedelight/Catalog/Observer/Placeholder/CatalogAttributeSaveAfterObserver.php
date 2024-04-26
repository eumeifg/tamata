<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Observer\Placeholder;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CatalogAttributeSaveAfterObserver implements ObserverInterface
{
    /**
     * @var Magedelight\Catalog\Model\Placeholder
     */
    protected $_resource;

    /**
     * @param \Magedelight\Catalog\Model\ResourceModel\Placeholder $placeholder
     */
    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\Placeholder $placeholder
    ) {
        $this->_resource = $placeholder;
    }

    /**
     * After save attribute, save attribute placeholder to md_eav_attribute_placeholder table.
     *
     * @param EventObserver $observer
     *
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        $customPlaceholders = $attribute->getAttributePlaceholder();
        
        if (is_array($customPlaceholders)) {
            $connection = $this->_resource->getConnection();
            $table = $this->_resource->getTable('md_eav_attribute_placeholder');
            $where = [
                'attribute_id = ?' => (int) $attribute->getId(),
            ];
            $connection->delete($table, $where);
            foreach ($customPlaceholders as $storeId => $placeholder) {
                if ($storeId == 0 || !strlen($placeholder)) {
                    continue;
                }
                $bind = ['attribute_id' => $attribute->getId(), 'store_id' => $storeId, 'value' => $placeholder];
                $connection->insert($table, $bind);
            }
        }

        return $this;
    }
}
