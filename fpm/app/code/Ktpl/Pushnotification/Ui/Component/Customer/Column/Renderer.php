<?php

namespace Ktpl\Pushnotification\Ui\Component\Customer\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Renderer extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepositoryInterface,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupCollection
    ) {
        $this->groupRepositoryInterface = $groupRepositoryInterface;
        $this->customerGroupCollection = $customerGroupCollection;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            $customerGroupOptions = $this->customerGroupCollection->toOptionArray();
            $customerGroupIds = [];
            $customerGroupIdExist = false;
            foreach ($customerGroupOptions as $key => $value) {
                $customerGroupIds [] = $value['value'];
            }

            $customer = [];
            foreach ($dataSource['data']['items'] as &$items) {
                //echo $items['send_to_customer_group'];die;
                if($items['send_to_customer_group']) {
                    $customerGroups = explode(',', $items['send_to_customer_group']);
                    foreach ($customerGroups as $customerGroup) {
                        if(in_array($customerGroup, $customerGroupIds)){
                            $customerGroupIdExist = true;
                            $group = $this->groupRepositoryInterface->getById($customerGroup);
                            $customer[] = $group->getCode();
                        }
                       
                       //echo "<pre>";print_r(get_class_methods($group));die;
                    }
                    //echo "<pre>";print_r($customer);die;

                    if($customerGroupIdExist){
                        $items['send_to_customer_group'] = implode(',', $customer);
                    }
                    unset($customer);
                }
            }
        }
        return $dataSource;
    }
}