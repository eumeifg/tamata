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
namespace Magedelight\Catalog\Block\Adminhtml\Product;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magedelight\Catalog\Model\Product;

class Edit extends FormContainer
{
 /**
  * Core registry
  *
  * @var \Magento\Framework\Registry
  */
    protected $coreRegistry = null;

    /**
     * constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {

        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Requestzones edit block
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_objectId = 'id';
        $this->_blockGroup = 'Magedelight_Catalog';
        $this->_controller = 'adminhtml_productRequest';

        parent::_construct();

        /*$this->buttonList->add(
            'saveandcontinue', array(
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'))
                )
            ), -100
        );*/
        
        $objId = $this->getRequest()->getParam($this->_objectId);

        if (!empty($objId)) {
            $model = $this->coreRegistry->registry('vendor_product');
            if (((int)$model->getData('status')) === Product::STATUS_UNLISTED) {
                $this->addButton(
                    'list',
                    [
                        'label' => __('List'),
                        'class' => 'list primary',
                        'onclick' => 'setLocation(\'' . $this->getListUrl() . '\')'
                    ]
                );
            }
            
            if (((int)$model->getData('status')) === Product::STATUS_LISTED) {
                $this->addButton(
                    'unlist',
                    [
                        'label' => __('Unlist'),
                        'class' => 'unlist primary',
                        'onclick' => 'setLocation(\'' . $this->getUnListUrl() . '\')'
                    ]
                );
            }
        }

        $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getBackUrl()  . '\')');
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $request = $this->coreRegistry->registry('vendor_product');
        if ($request->getId()) {
            return __("Edit Product '%1'", $this->escapeHtml($request->getName()));
        } else {
            return __('New Product');
        }
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('request_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'request_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'request_content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
    
    /**
     * @return string
     */
    public function getListUrl()
    {
        return $this->getUrl('*/*/listproduct', ['id' => $this->getRequest()->getParam($this->_objectId)]);
    }
    
    /**
     * @return string
     */
    public function getUnListUrl()
    {
        return $this->getUrl('*/*/unlistproduct', ['id' => $this->getRequest()->getParam($this->_objectId)]);
    }
    
    public function getBackUrl()
    {
        $model = $this->coreRegistry->registry('vendor_product');
        
        if (((int)$model->getData('status')) === Product::STATUS_LISTED) {
            return $this->getUrl('rbcatalog/product/index', ['status' => 1]);
        } else {
            return $this->getUrl('rbcatalog/product/index', ['status' => 0]);
        }
        parent::getBackUrl();
    }
}
