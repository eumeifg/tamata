<?php

namespace Ktpl\Productslider\Block\Adminhtml\Slider\Edit\Tab\Renderer;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Ktpl\Productslider\Block\Adminhtml\Slider\Edit\Tab\Renderer\Type;

class BrandVendor extends AbstractFieldArray
{
    private $_type;

    private $_image;

    protected $_template = 'Ktpl_Productslider::array_extended.phtml';

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('title', ['label' => __('Title'), 'renderer' => false]);
        $this->addColumn('type', ['label' => __('Type'), 'renderer' => $this->getType()]);
        $this->addColumn('data_id', ['label' => __('Data ID'), 'renderer' => false]);
        $this->addColumn('image', ['label' => __('Image'), 'renderer' => $this->getImage()]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $type = $row->getType();
        if ($type !== null) {
            $options['option_' . $this->getType()->calcOptionHash($type)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    private function getType() {
        if (!$this->_type) {
            $this->_type = $this->getLayout()->createBlock(
                Type::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_type;
    }

    private function getImage() {
        if (!$this->_image) {
            $this->_image = $this->getLayout()->createBlock(
                Thumbnail::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_image;
    }
}
