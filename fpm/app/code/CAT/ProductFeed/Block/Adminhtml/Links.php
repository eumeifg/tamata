<?php
 
namespace CAT\ProductFeed\Block\Adminhtml;

use Magento\Framework\Data\Form\Element\AbstractElement;
use CAT\ProductFeed\Helper\Data as FbFeedHelper;

/**
 * Links class
 */
class Links extends \Magento\Config\Block\System\Config\Form\Field
{
    
    /**
     * @var FbFeedHelper
     */
    public $helper;
    
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Apptrian\FacebookCatalog\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        FbFeedHelper $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }
    
    /**
     * @param AbstractElement $element
     * @return void
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $element = null;
        $data = $this->helper->getProductFeedUrls();
        $html = '<style>.accordion .config .value.with-tooltip {font-size: 1em;}</style><div>';
        
        foreach ($data as $d) {
            $html .= '<p><span>' . $d['name'] . ':</span><br />';
            $html .= '<a href="'. $d['url'] . '" download="' . $d['filename']
                . '" target="_blank">' . $d['url'] . '</a></p>';
        }
        $html .= '</div>';
        return $html;
    }
}
