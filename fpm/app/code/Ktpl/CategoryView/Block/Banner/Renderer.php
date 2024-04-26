<?php

namespace Ktpl\CategoryView\Block\Banner;

class Renderer extends \Magento\Framework\View\Element\AbstractBlock
{
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

     /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

	

    /**
     * Prepare HTML content
     *
     * @return string
     */
    protected function _toHtml()
    {
    	$bannerBlock = $this->getCurrentCategory()->getBannerBlock();    	
    	
        return "";
    }

    public function getCurrentCategory()
    {
        return $this->_coreRegistry->registry('current_category');
    }
}
