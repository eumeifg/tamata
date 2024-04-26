<?php

namespace MDC\Sales\Block\Adminhtml\Import;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class View extends Template
{
    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * View constructor.
     * @param Template\Context $context
     * @param FormKey $formKey
     * @param AssetRepository $assetRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        FormKey $formKey,
        AssetRepository $assetRepository,
        array $data = []
    ) {
        $this->formKey = $formKey;
        $this->assetRepository = $assetRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getFormTitle()
    {
        return __('Bulk Import SubOrders');
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSampleFilePath() {
        return $this->assetRepository->getUrl('MDC_Sales::sample_files/');
    }
}