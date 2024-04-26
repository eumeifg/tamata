<?php

declare(strict_types=1);

namespace MDC\GetItTogether\Api\Data;

/**
 * Interface OrderGetItTogetherInterface
 * @api
 */
interface OrderGetItTogetherInterface
{
    const COLUMN_NAME = 'get_it_together';

     /**
     * Get Is together or not
     *
     * @return string|int
     */
    public function getGetItTogether();

    /**
     * Set Is together or not
     * @param string|int $getItTogether
     * @return $this
     */
    public function setGetItTogether($getItTogether);
}
