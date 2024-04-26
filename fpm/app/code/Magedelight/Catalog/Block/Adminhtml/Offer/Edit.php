<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Adminhtml\Offer;

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
        \Magedelight\Catalog\Helper\Offer $offerHelper,
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
        $this->_blockGroup = 'Magedelight_Catalog';
        $this->_mode = 'create';
        
        parent::_construct();

        $this->setId('create_vendor_offer');
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
        return __('Create New Vendor Offer');
    }
}
