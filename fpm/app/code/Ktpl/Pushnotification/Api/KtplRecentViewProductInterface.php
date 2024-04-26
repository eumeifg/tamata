<?php declare(strict_types=1);
/**
 * Copyright © Magedelight, All rights reserved.
 */
namespace Ktpl\Pushnotification\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface for Add recently view product
 * @api
 */

interface KtplRecentViewProductInterface
{
	/**
     * Add recently view product
     * @param int $productId The Product ID.
     * @param string $deviceTokenId The Device Token ID.
     * @return string.
     * @throws \Magento\Framework\Exception\LocalizedException.
    */

    public function recentlyViewdProduct( $productId, $deviceTokenId );
}