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
namespace Magedelight\OffersImportExport\Controller\Adminhtml\Index;

class VendorList extends \Magedelight\Vendor\Controller\Adminhtml\Index\VendorList
{

    /**
     *
     * @param $websiteId
     * @return array
     */
    protected function getVendors($websiteId = null)
    {
        $collection = $this->vendorFactory->create()->getCollection();
        $collection->getSelect()->joinLeft(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = main_table.vendor_id and rvwd.status = 1',
            ['vendor_id','business_name','name','status']
        );
        $collection->addFieldToFilter(
            'main_table.email',
            ['neq'=>\Magedelight\Vendor\Model\Vendor::ADMIN_VENDOR_EMAIL]
        );
        $collection->addFieldToFilter('rvwd.email_verified', ['eq' => 1]);
        if ($websiteId) {
            $collection->addFieldToFilter('rvwd.website_id', ['eq' => $websiteId]);
        }
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
                $label = $venList['name'] . ' (Id - ' . $venList['vendor_id'] . ', '
                    . $this->vendorStatuses->getOptionText($venList['status']) . ')';
                if (empty($venList['name'])) {
                    continue;
                }
            } else {
                $label = $venList['business_name'] . ' (Id - ' . $venList['vendor_id'] . ', '
                    . $this->vendorStatuses->getOptionText($venList['status']) . ')';
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
