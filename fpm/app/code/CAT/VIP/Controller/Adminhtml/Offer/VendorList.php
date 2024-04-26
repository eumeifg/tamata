<?php

namespace CAT\VIP\Controller\Adminhtml\Offer;

use Magento\Framework\Json\EncoderInterface;

class VendorList extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magedelight\Vendor\Model\Source\Status $vendorStatuses
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param EncoderInterface $jsonEncoder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Vendor\Model\Source\Status $vendorStatuses,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        EncoderInterface $jsonEncoder
    ) {
        $this->_vendorProductFactory = $vendorProductFactory->create();
        $this->vendorFactory = $vendorFactory;
        $this->vendorStatuses = $vendorStatuses;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context);
    }

    /**
     * Get available vendors list
     */
    public function execute()
    {
        $websiteId = $this->getRequest()->getParam('website');
        $productId = $this->getRequest()->getParam('product_id');
        $vendorProductCollection = $this->_vendorProductFactory->getCollection()
            ->addFieldToFilter('marketplace_product_id', $productId);
        $ignorVendor = [];
        if (count($vendorProductCollection->getData())) {
            $vendorData = $vendorProductCollection->getData();
            foreach ($vendorData as $vedorProData) {
                $ignorVendor[] = $vedorProData['vendor_id'];
            }
        }

        $vendorListOptions = '';
        if ($websiteId) {
            $vendors = $this->getVendors($websiteId);

            foreach ($vendors as $vendor) {
                if (in_array($vendor['value'], $ignorVendor)) {
                    $vendorListOptions .= "<option value='" . $vendor['value'] . "'>" . $vendor['label'] . "</option>";
                }
            }
        } else {
            $vendorListOptions .= "<option value=''>-- " . __('Please Select Website') . " --</option>";
        }

        $result['htmlcontent'] = $vendorListOptions;
        $this->getResponse()->representJson(
            $this->jsonEncoder->encode($result)
        );
    }

    /**
     *
     * @param type $websiteId
     * @return type
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
