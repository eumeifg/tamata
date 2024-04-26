<?php
namespace Ktpl\Pushnotification\Model\Customer;

use \Magento\Framework\Data\OptionSourceInterface;

class OptionSource implements OptionSourceInterface
{
    protected $_collectionFactory;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $_collectionFactory
    ) {
        $this->_collectionFactory = $_collectionFactory;
    }

   /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array("value" => "<value>", "label"=> "<label>"), ...)
     */
    public function toOptionArray()
    {
        /**
         * @var $groupCollection \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
         */

        $customerCollection = $this->_collectionFactory->create();
        $options = [];
        foreach ($customerCollection as $customer) {
            //echo $customer->getName(); die;
            $options[] = [
                'label' => $customer->getName(),
                'value' => $customer->getId()
            ];
        }
        return $options;
    }
}
