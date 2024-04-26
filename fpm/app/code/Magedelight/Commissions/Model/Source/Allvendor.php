<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Allvendor implements OptionSourceInterface
{
    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     */
    public function __construct(
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory
    ) {
        $this->vendorFactory = $vendorFactory;
    }

    public function toOptionArray()
    {
        $collection = $this->vendorFactory->create()->getCollection();
        $collection->addFieldToFilter('main_table.email', ['neq'=>'admin@gmail.com']);
        $collection->getSelect()->join(
            ['rvc'=>'md_vendor_commissions'],
            'rvc.vendor_id = main_table.vendor_id',
            ['rvc.vendor_commission_id']
        );
        $collection->getSelect()->joinLeft(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = main_table.vendor_id',
            ['rvwd.vendor_id','rvwd.business_name','rvwd.name']
        );
        $collection->getSelect()->group('main_table.vendor_id');

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
