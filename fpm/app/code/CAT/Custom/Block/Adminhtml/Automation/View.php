<?php

namespace CAT\Custom\Block\Adminhtml\Automation;

use Magento\Backend\Block\Template;
use CAT\Custom\Model\Source\Option;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\WebsiteFactory;

class View extends Template
{
    /**
     * @var Option
     */
    protected $_option;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @var WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @param Template\Context $context
     * @param Option $option
     * @param FormKey $formKey
     * @param AssetRepository $assetRepository
     * @param WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Option $option,
        FormKey $formKey,
        AssetRepository $assetRepository,
        WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        $this->_option = $option;
        $this->formKey = $formKey;
        $this->assetRepository = $assetRepository;
        $this->websiteFactory = $websiteFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getFormTitle()
    {
        return __('Import Bulk Sheets');
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSampleFilePath() {
        return $this->assetRepository->getUrl('CAT_Custom::sample_files/');
    }

    /**
     * @return mixed
     */
    public function getImportOptions() {
        return $this->_option->toOptionArray();
    }

    public function getWebsiteOptions() {
        $websites = $this->websiteFactory->create()->getCollection()->toOptionArray();
        array_unshift($websites, ['value' => '', 'label' => __('--- Please Select Website ---')]);
        return $websites;
    }

    public function getVendorListUrl()
    {
        return $this->getUrl('vendoroffers/index/vendorList');
    }
}
