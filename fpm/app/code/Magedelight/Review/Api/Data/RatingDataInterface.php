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

namespace Magedelight\Review\Api\Data;

//use Magento\Tests\NamingConvention\true\string;

/**
 * @api
 */
interface RatingDataInterface
{

    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const RATING_ID = 'rating_id';
    const RATING_CODE = 'rating_code';
    const RATING_VALUE = 'rating_value';

    /**
     * @param int $ratingId
     * @return $this
     */
    public function setRatingId($ratingId);

   /**
    * @return int $ratingId
    */
    public function getRatingId();

   /**
    * @param string $ratingCode
    * @return $this
    */
    public function setRatingCode($ratingCode);

   /**
    * @return string $ratingCode
    */
    public function getRatingCode();

   /**
    * @param int $ratingValue
    * @return $this
    */
    public function setRatingValue($ratingValue);

   /**
    * @return int $ratingValue
    */
    public function getRatingValue();
}
