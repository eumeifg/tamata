<?php

namespace Ktpl\CategoryView\Block\Adminhtml;

class Options extends \Magento\Backend\Block\Template
{
    const TYPE_NONE = 0;
    const TYPE_SECTION = 1;
    const TYPE_BLOCK = 2;
    
	/**
     * @var string
     */
    protected $_template = 'Ktpl_CategoryView::options.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}