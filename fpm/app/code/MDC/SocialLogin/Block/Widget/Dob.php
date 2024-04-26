<?php
namespace MDC\SocialLogin\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Api\ArrayObjectSearch;

class Dob extends \Magento\Customer\Block\Widget\Dob
{
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        \Magento\Framework\View\Element\Html\Date $dateElement,
        \Magento\Framework\Data\Form\FilterFactory $filterFactory,
        array $data = []
    ) {
        parent::__construct($context, $addressHelper, $customerMetadata, $dateElement, $filterFactory);
    }
    /**
     * Return data-validate rules
     *
     * @return string
     */
    public function getHtmlExtraParams()
    {
        $validators = [];
        if ($this->isRequired()) {
            $validators['required'] = true;
        }
        $validators['validate-date'] = [
            'dateFormat' => $this->getDateFormat()
        ];
        $validators['validate-dob'] = [
            'dateFormat' => $this->getDateFormat()
        ];

        return 'data-validate="' . $this->_escaper->escapeHtml(json_encode($validators)) . '"';
    }
}