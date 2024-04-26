<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Model;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ResourceConnection;

class ConfigurableProductResolver
{

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * RemoveProductsWithNoChild constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param array $parentIds
     */
    public function deleteParentProductsWithNoChild(array $parentIds = [])
    {
        $entityIds = [];
        $select = $this->getConnection()->select()->from(
            ['mdvp' => $this->getPrimaryTable()],
            ['mdvp.marketplace_product_id', 'COUNT(mdvp_child.vendor_product_id) as total_childrens']
        )->joinLeft(
            ['mdvp_child' => $this->getPrimaryTable()],
            'mdvp_child.parent_id = mdvp.marketplace_product_id and mdvp_child.type_id = "' . Type::TYPE_SIMPLE . '"',
            ['mdvp_child.parent_id']
        );
        $select->where('mdvp.type_id = "' . Configurable::TYPE_CODE . '"');
        if (!empty($parentIds)) {
            $select->where('mdvp.marketplace_product_id IN (?)', $parentIds);
        }
        $select->group('mdvp.marketplace_product_id');

        $selectMain = $this->getConnection()->select()->from(
            ['result' => $select],
            [
                'marketplace_product_id'
            ]
        )->where('total_childrens < 1');

        $entityIds = $this->getConnection()->fetchCol($selectMain);
        $this->deleteData($entityIds);
    }

    /**
     * @return string
     */
    protected function getPrimaryTable()
    {
        return 'md_vendor_product';
    }

    /**
     * @param array $entityIds
     */
    protected function deleteData(array $entityIds = [])
    {
        if (empty($entityIds)) {
            return;
        }

        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getPrimaryTable()],
            null
        )->where('main_table.marketplace_product_id IN (?)', $entityIds);
        $query = $select->deleteFromSelect('main_table');
        $this->getConnection()->query($query);
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection()
    {
        return $this->resource->getConnection();
    }
}
