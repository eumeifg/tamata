<?php
namespace Ktpl\BannerManagement\Block;

use Magento\Framework\View\Element\Template;

class WysiwygBlock extends Template
{

    protected $templateProcessor;
    public function __construct(
        Template\Context $context,
        \Zend_Filter_Interface $templateProcessor
    ) {
        $this->templateProcessor = $templateProcessor;
        parent::__construct($context);
    }


    public function filterOutputHtml($string)
    {
        return $this->templateProcessor->filter($string);
    }
}
