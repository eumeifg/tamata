<?php

namespace CAT\VIP\Block\Adminhtml\Offer;

use Magento\Framework\Translate\InlineInterface;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magedelight\Catalog\Helper\Offer
     */
    public $offerHelper;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \CAT\VIP\Helper\Offer $offerHelper,
        array $data = []
    ) {
        $this->offerHelper = $offerHelper;
        parent::__construct($context, $data);
    }
    
    /**
     *
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_offer';
        $this->_blockGroup = 'CAT_VIP';
        $this->_mode = 'create';
        
        parent::_construct();

        $this->setId('create_vip_offer');
        $this->removeButton('reset');
        $this->removeButton('save');
        $this->removeButton('back');
        $this->removeButton('delete');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        return __('Create New VIP Offer');
    }
}
