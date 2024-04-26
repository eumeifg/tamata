<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model\Config\Source;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;

class Vendors implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    public function __construct(
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory
    ) {
        $this->vendorFactory = $vendorFactory;
    }

    public function toOptionArray()
    {
        $collection = $this->vendorFactory->create()->getCollection();
        $collection->addFieldToFilter('main_table.email', ['neq'=>'admin@gmail.com']);
        $statuses = [VendorStatus::VENDOR_STATUS_ACTIVE,VendorStatus::VENDOR_STATUS_VACATION_MODE];
        $collection->getSelect()->joinLeft(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = main_table.vendor_id AND rvwd.status IN (' . implode(',', $statuses) . ')',
            ['rvwd.vendor_id','rvwd.business_name','rvwd.name']
        );

        $options = [
            [
               'value'=>'',
               'label'=>__('--Please Select--')
            ]
        ];

        $valuesToSort[]='';
        $labelsToSort[]=__('--Please Select--');

        foreach ($collection as $venList) {
            if (empty($venList['business_name'])) {
                $label = $venList['name'];
                if (empty($venList['name'])) {
                    continue;
                }
            } else {
                $label = $venList['business_name'];
            }
            $options[] = [
                   'value'=>$venList['vendor_id'],
                   'label'=>$label
            ];
            $valuesToSort[]  = $venList['vendor_id'];
            $labelsToSort[] = $label;
        }

        array_multisort($labelsToSort, SORT_ASC|SORT_NATURAL|SORT_FLAG_CASE, $valuesToSort, SORT_DESC, $options);
        return $options;
    }
}
