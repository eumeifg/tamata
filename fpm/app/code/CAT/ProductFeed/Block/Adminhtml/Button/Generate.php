<?php
 
namespace CAT\ProductFeed\Block\Adminhtml\Button;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Generate extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param AbstractElement $element
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $element = null;
        
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Button::class);
       
        $url = $this->getUrl("fbfeed/generate/index");
            
        $data = [
            'class'   => 'fbfeed-admin-button-generate',
            'label'   => __('Generate Product Feed'),
            'onclick' => "setLocation('" . $url . "')",
        ];
        
        $html = $buttonBlock->setData($data)->toHtml();
        
        return $html;
    }
}
