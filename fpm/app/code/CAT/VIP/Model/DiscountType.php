<?php


namespace CAT\VIP\Model;

class DiscountType implements \Magento\Framework\Option\ArrayInterface {

	const STATUS_FIXED = "Fixed";

	const STATUS_DISCOUNT = 'Percentage';
	protected $_customerGroup;

	public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
    ) {
        $this->_customerGroup = $customerGroup; 
    }

	public function toOptionArray() {

		return [

					[

					self::STATUS_FIXED => __('Fixed'),

					self::STATUS_DISCOUNT => __('Percentage'),

					],

			];

	}

	public static function getOptionArray()

	{

		return [self::STATUS_FIXED => __('Fixed'), self::STATUS_DISCOUNT => __('Percentage')];

	}

	public function getOptionArray2()
    {

        $customerGroups = $this->_customerGroup->toOptionArray();

        $data_arrays=array(); 

        foreach($customerGroups as $cg){
          $data_arrays[$cg['value']]=$cg['label'];
        }
        return($data_arrays);        
    }

}