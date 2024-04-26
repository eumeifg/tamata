<?php

namespace Ktpl\Warehousemanagement\Ui\Component\Listing\Column;

use Magento\User\Model\ResourceModel\User\CollectionFactory;

class ExecutiveStatus implements \Magento\Framework\Option\ArrayInterface
{
	public function __construct(
	    CollectionFactory $userCollectionFactory
	)
	{
	    $this->userCollectionFactory = $userCollectionFactory;
	}
    public function toOptionArray()
    {
    	foreach ($this->userCollectionFactory->create() as $user) {
	        $adminUsers[] = [
	            'value' => $user->getId(),
	            'label' => $user->getName()
	        ];
	    }
	    return $adminUsers;
    }
}
