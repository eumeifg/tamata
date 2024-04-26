<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Api\Data;

/**
* @api
* Only for enteprise edition to set the vendor product price 
* to be displayed in products fetched based on related rule
*/

interface RelatedDataInterface
{
    /**
     * Get Related Product Min Price
     *
     * @return float
     */
    public function getMinPrice();

    /**
     * Set Related Product Min Price
     * @param float $minPrice
     * @return $this
     */
    public function setMinPrice($minPrice);

    /**
     * Get Media Path
     *
     * @return float
     */
    public function getMinSpecialPrice();

    /**
     * Set Media Path
     * @param float $minSpPrice
     * @return $this
     */
    public function setMinSpecialPrice($minSpPrice);
     
}
