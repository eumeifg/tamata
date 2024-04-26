<?php

namespace CAT\AIRecommend\Api;
use CAT\AIRecommend\Api\Data\RecommenderBuilderResultsInterface;
interface RecommenderBuilderInterface
{
    /**
     * @api
     * @param string $item_id
     * @return RecommenderBuilderResultsInterface
     */

    public function itembase($item_id);

    /**
     * @api
     * @param string $item_id
     * @return RecommenderBuilderResultsInterface
     */

    public function fashionbase($item_id);


    /**
     * @api
     * @param int $customerId The customer ID.
     * @return RecommenderBuilderResultsInterface
     */

    public function marketbase($customerId);

    /**
     * @api
     * @param int $customerId The customer ID.
     * @return RecommenderBuilderResultsInterface
     */

    public function userbase($customerId);


    /**
     * @api
     * @param int $customerId The customer ID.
     * @return RecommenderBuilderResultsInterface
     */

    public function eventbase($customerId = null);

}
