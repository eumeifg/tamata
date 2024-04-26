<?php

/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Review
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Review\Api;

/**
 * @api
 */
interface ReviewInterface
{

    /**
     * @param int|null $limit;
     * @param int|null $currPage;
     * @param int|null $storeId;
     * @return \Magedelight\Review\Api\Data\ReviewCollectionInterface $reviewData
     */
    public function getReviewsList($limit = null, $currPage = null, $storeId = null);

    /**
     * @param int $productId;
     * @param int|null $storeId;
     * @param int|null $currPage;
     * @return \Magedelight\Review\Api\Data\ReviewCollectionInterface $reviewData
     */
    public function getProductReviewsList($productId, $storeId = null, $currPage = null);

    /**
     * @param Magedelight\Review\Api\Data\ReviewDataInterface $reviewData;
     * @return void
     */
    public function writeReviews(\Magedelight\Review\Api\Data\ReviewDataInterface $reviewData);

    /**
     * @api
     * @param string $entityCode;
     * @return mixed
     */
    public function getRatingCode($entityCode);
}
