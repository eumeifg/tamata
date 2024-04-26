<?php

namespace Ktpl\Pushnotification\Model\Config\Backend;

class EnvironmentOptions implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
	  return [
	    ['value' => 'sandbox', 'label' => __('Sandbox')],
	    ['value' => 'production', 'label' => __('Production')]
	  ];
	}
}
