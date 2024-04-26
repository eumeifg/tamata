<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\Productslider\Model;

use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Ktpl\Productslider\Model\Calculator;

/**
 * Class Renderer
 */
class Renderer
{
	/**
     * @var Calculator
     */
    private $calculator;

    /**
     * Initialize dependencies.
     *
     * @param Calculator $calculator
     */
    public function __construct(
        Calculator $calculator
    ) {
        $this->calculator = $calculator;
    }

    /**
     * Create Renderer to render the offer label
     *
     * @param FinalPriceBox $subject
     * @param string $result
     * @return string
     */
	public function execute(FinalPriceBox $subject, $result)
	{
		$value = $this->calculator->execute($subject);
		$productId = $subject->getSaleableItem()->getId();
		$ProductIdAppedendeClass = "discount-box discount-box-".$productId;
		// if($value > 0)
		// {
		// 	$result .= '<div class="'.$ProductIdAppedendeClass.'">
	 //        			    <span>' . __("%1% off ", $value) . '</span>
  //           			</div>';
		// }

		return $result;
	}
}