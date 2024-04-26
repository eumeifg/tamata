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
namespace Magedelight\Vendor\Controller\Adminhtml\Microsite\Index;

use Magento\Framework\Json\EncoderInterface;

class VendorList extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magedelight\Vendor\Model\Source\Status $vendorStatuses
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Escaper $escaper
     * @param EncoderInterface $jsonEncoder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Vendor\Model\Source\Status $vendorStatuses,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Escaper $escaper,
        EncoderInterface $jsonEncoder
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->vendorStatuses = $vendorStatuses;
        $this->jsonEncoder = $jsonEncoder;
        $this->storeManager = $storeManager;
        $this->_systemStore = $systemStore;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Get available vendors list
     */
    public function execute()
    {
        $websiteId = $this->getRequest()->getParam('website');
        $vendorListOptions = '';
        if ($websiteId) {
            $vendors = $this->getVendors($websiteId);

            foreach ($vendors as $vendor) {
                $vendorListOptions .= "<option value='" . $vendor['value'] . "'>" . $vendor['label'] . "</option>";
            }
        } else {
            $vendorListOptions .= "<option value=''>-- " . __('Please Select Website') . " --</option>";
        }

        $result['htmlcontent']['vendors'] = $vendorListOptions;
        $result['htmlcontent']['stores'] = $this->getFilteredStores($websiteId);
        $this->getResponse()->representJson(
            $this->jsonEncoder->encode($result)
        );
    }

    /**
     * @param type $websiteId
     * @return string
     */
    protected function getFilteredStores($websiteId = null)
    {
        $websiteCollection = $this->_systemStore->getWebsiteCollection();
        $groupCollection = $this->_systemStore->getGroupCollection();
        $storeCollection = $this->_systemStore->getStoreCollection();
        $html = '';
        foreach ($websiteCollection as $website) {
            if ($website->getId() != $websiteId) {
                continue;
            }
            $websiteShow = false;
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeCollection as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $html .= '<optgroup label="' . $this->escaper->escapeHtml($website->getName()) . '"></optgroup>';
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $html .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $this->escaper->escapeHtml(
                            $group->getName()
                        ) . '">';
                    }
                    $value = '';
                    $selected = $value == $store->getId() ? ' selected="selected"' : '';
                    $html .= '<option value="' .
                        $store->getId() .
                        '"' .
                        $selected .
                        '>&nbsp;&nbsp;&nbsp;&nbsp;' .
                        $this->escaper->escapeHtml(
                            $store->getName()
                        ) . '</option>';
                }
                if ($groupShow) {
                    $html .= '</optgroup>';
                }
            }
        }
        return $html;
    }

    /**
     *
     * @param type $websiteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
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
            /* Get those vendors in vendorlist whose microsite is not created yet. */
            $stores = $this->storeManager->getWebsite($websiteId)->getStoreIds();
            if (!empty($stores) && is_array($stores)) {
                $collection->getSelect()->where('main_table.vendor_id NOT IN (SELECT vendor_id from md_vendor_microsites where store_id IN (' . implode($stores) . '))');
            }
            /* Get those vendors in vendorlist whose microsite is not created yet. */
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
                $label = $venList['name'] . ' (' . $this->vendorStatuses->getOptionText($venList['status']) . ')';
                if (empty($venList['name'])) {
                    continue;
                }
            } else {
                $label = $venList['business_name'] . ' ('
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
