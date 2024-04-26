<?php

namespace Ktpl\Productslider\Block\Adminhtml\Slider\Edit\Tab\Renderer;

class Thumbnail extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @return image html
     */
    protected function _toHtml()
    {
        $html = '';
        $html .= '<input type="file" accept="image/png, image/jpeg" name="' . $this->getInputName() . '" id="' . $this->getInputId() . '" />';
        $html .= '<input type="hidden" name="' . $this->getInputName() . '" id="' . $this->getInputId() . 's" >';
        $html .= '<img name="uploaded_image_' . $this->getInputName() . '_img" id="' . $this->getInputId() . 's_img" width="50" style="border:2px solid grey;margin: 2% 2%;">';
        return $html;
    }
}
