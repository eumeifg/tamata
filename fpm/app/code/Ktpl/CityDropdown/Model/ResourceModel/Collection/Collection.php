<?php

namespace Ktpl\CityDropdown\Model\ResourceModel\Collection;

use Ktpl\CityDropdown\Model\City as CityModel;
use Ktpl\CityDropdown\Model\ResourceModel\City  as CityResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    //@codingStandardsIgnoreLine
    protected $_idCity = 'entity_id';

    /**
     * Init resource model
     * @return void
     */
    public function _construct()
    {

        $this->_init(
            CityModel::class,
            CityResourceModel::class
        );
        $this->_map['city']['entity_id'] = 'main_table.entity_id';
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        return $this;
    }
}