<?php

namespace MDC\Catalog\Pricing\Render; 

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
	public function getCacheLifetime()
    {
            return null;
    }
}