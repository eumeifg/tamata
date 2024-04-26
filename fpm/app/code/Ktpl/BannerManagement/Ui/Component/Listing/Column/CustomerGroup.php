<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Ui\Component\Listing\Column;

use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class CustomerGroup
 *
 * @package Ktpl\BannerManagement\Ui\Component\Listing\Column
 */
class CustomerGroup extends Column
{
    /**
     * @var GroupCollection
     */
    protected $customerGroup;

    /**
     * CustomerGroup constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param GroupCollection    $GroupCollection
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        GroupCollection $GroupCollection,
        array $components = [],
        array $data = []
    ) {
        $this->customerGroup = $GroupCollection;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $item[$this->getData('name')] = explode(',', $item[$this->getData('name')]);
                    $item[$this->getData('name')] = $this->prepareItem($item);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param array $item
     *
     * @return string
     */
    public function prepareItem(array $item)
    {
        $content   = [];
        $origGroup = $item['customer_group_ids'];

        if (!is_array($origGroup)) {
            $origGroup = [$origGroup];
        }

        $customer = $this->customerGroup->toOptionArray();
        foreach ($origGroup as $group) {
            $content[] = $customer[$group]['label'];
        }

        return implode(", ", $content);
    }
}
