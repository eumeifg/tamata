<?php

namespace MDC\Microsite\Block\Product\View;

class ProductVendor extends \Magedelight\Catalog\Block\Product\View\ProductVendor
{
	
	public function getVendorMicrositeUrl()
    {
        
        if($this->_micrositeHelper->getVendorMicrositeUrl($this->getProductDefaultVendorId()) == null){

            return $this->getUrl('microsite/products/vendor', ['vid' => $this->getProductDefaultVendorId()] );
        }else{
            return $this->_micrositeHelper->getVendorMicrositeUrl($this->getProductDefaultVendorId());

        }
        
    }
}