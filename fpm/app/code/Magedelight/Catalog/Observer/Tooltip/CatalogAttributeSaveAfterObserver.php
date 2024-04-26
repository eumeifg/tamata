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
namespace Magedelight\Catalog\Observer\Tooltip;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CatalogAttributeSaveAfterObserver implements ObserverInterface
{

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Tooltip
     */
    protected $_resource;

    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\Tooltip $tooltip
    ) {
        $this->_resource = $tooltip;
    }

    /**
     * After save attribute, save attribute description to eav_attribute_description table.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        $storeDescriptions = $attribute->getFrontendDescription();
        if (is_array($storeDescriptions)) {
            $connection = $this->_resource->getConnection();
            if ($attribute->getId()) {
                $condition = ['attribute_id = ?' => (int) $attribute->getId()];
                $connection->delete($this->_resource->getTable('md_eav_attribute_tooltip'), $condition);
            }
            foreach ($storeDescriptions as $storeId => $description) {
                if ($storeId == 0 || !strlen($description)) {
                    continue;
                }
                $bind = ['attribute_id' => $attribute->getId(), 'store_id' => $storeId, 'value' => $description];
                $connection->insert($this->_resource->getTable('md_eav_attribute_tooltip'), $bind);
            }
        }
        return $this;
    }
}
