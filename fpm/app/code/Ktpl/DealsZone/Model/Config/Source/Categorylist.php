<?php

namespace Ktpl\DealsZone\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Categorylist implements ArrayInterface
{
    protected $_categoryCollection;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection)
    {
        $this->_categoryCollection = $categoryCollection;
    }


    /*  
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value)
        {

            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $ret;
    }

    /*
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {
        $categories = $this->_categoryCollection->addAttributeToSelect('*');

        $catagoryList = array();
        foreach ($categories as $category) {
            $catagoryList[$category->getEntityId()] = __($category->getName());
        }
        return $catagoryList;
    }

}