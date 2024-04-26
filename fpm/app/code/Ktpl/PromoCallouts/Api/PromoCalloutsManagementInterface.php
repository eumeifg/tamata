<?php

namespace Ktpl\PromoCallouts\Api;

interface PromoCalloutsManagementInterface
{

    /**
     * GET Bestseller Vendors
     * @return \Ktpl\PromoCallouts\Api\Data\VendorInterface[]
     */
    public function getPromoVendors();
}
