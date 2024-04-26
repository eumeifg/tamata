<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\Productslider\Model;

use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Magento\Catalog\Pricing\Price;

/**
 * Class Calculator
 */
class Calculator
{
    /**
     * Calculate offer percentage
     *
     * @param FinalPriceBox $subject
     * @return int
     */
	public function execute(FinalPriceBox $subject)
	{
        /** Fetch regular price value from price box object */
        $regularPrice = $subject->getPriceType(Price\RegularPrice::PRICE_CODE)->getAmount()->getValue();

        /** Fetch special price value from price box object */
        $specialPrice = $subject->getPriceType(Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();

        /** check regular price and special price and calculate the percentage */
    	if(isset($regularPrice) && floatval($regularPrice) > 0
    		&& isset($specialPrice) && floatval($specialPrice) > 0
    		&& floatval($regularPrice) > floatval($specialPrice)
    	) {
    		return round(($regularPrice - $specialPrice) * 100 / $regularPrice);
    	}

        return 0;
	}
}