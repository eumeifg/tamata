<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\Productslider\Plugin;

use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Ktpl\Productslider\Model\Renderer;

/**
 * Class AddLabelToFinalPriceBox
 */
class AddLabelToFinalPriceBox
{
	/**
     * @var Renderer
     */
    private $renderer;

    /**
     * Initialize dependencies.
     *
     * @param Renderer $renderer
     */
    public function __construct(
        Renderer $renderer
    ) {
        $this->renderer = $renderer;
    }

    /**
     * Append Offer Label to PriceBox Html
     *
     * @param FinalPriceBox $subject
     * @return string $result
     * @return string
     */
	public function afterToHtml(
		FinalPriceBox $subject,
		$result
	) {
		return $this->renderer->execute($subject, $result);
	}
}