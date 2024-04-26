<?php

namespace Ktpl\DealsZone\Api;

interface DealsZoneManagementInterface
{

    /**
     * GET Deals Zone Categories
     * @param int|null $storeId
     * @return \Ktpl\DealsZone\Api\Data\DealZoneSearchResultsInterface
     */
    public function getDealsZone($storeId = null);
}
