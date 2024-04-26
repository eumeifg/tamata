<?php
namespace Magedelight\Customer\Api;

/**
 * @api
 */
interface MobileDashboardInterface
{
    /**
     * Get Customer Dashboard data for Mobile
     * @return \Magedelight\Customer\Api\Data\MobileDashboardDataInterface
     */
    public function getMobileDashboard();
}
