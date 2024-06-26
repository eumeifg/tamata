<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Block\Widget\Form;

/**
 * Backend form container block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Container extends \Magedelight\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_objectId = 'id';

    /**
     * @var string[]
     */
    protected $_formScripts = [];

    /**
     * @var string[]
     */
    protected $_formInitScripts = [];

    /**
     * @var string
     */
    protected $_mode = 'edit';

    /**
     * @var string
     */
    protected $_blockGroup = 'Magedelight_Backend';

    /**
     * @var string
     */
    protected $_template = 'Magedelight_Backend::widget/form/container.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                'class' => 'back'
            ],
            -1
        );
        $this->addButton(
            'reset',
            ['label' => __('Reset'), 'onclick' => 'setLocation(window.location.href)', 'class' => 'reset'],
            -1
        );

        $objId = $this->getRequest()->getParam($this->_objectId);

        if (!empty($objId)) {
            $this->addButton(
                'delete',
                [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\')'
                ]
            );
        }

        $this->addButton(
            'save',
            [
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ]
            ],
            1
        );
    }

    /**
     * Create form block
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->_blockGroup && $this->_controller && $this->_mode && !$this->_layout->getChildName(
            $this->_nameInLayout,
            'form'
        )
        ) {
            $this->addChild('form', $this->_buildFormClassName());
        }
        return parent::_prepareLayout();
    }

    /**
     * Build child form class name
     *
     * @return string
     */
    protected function _buildFormClassName()
    {
        return $this->nameBuilder->buildClassName(
            [$this->_blockGroup, 'Block', $this->_controller, $this->_mode, 'Form']
        );
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', [$this->_objectId => $this->getRequest()->getParam($this->_objectId)]);
    }

    /**
     * Get form save URL
     *
     * @see getFormActionUrl()
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getFormActionUrl();
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/*/save');
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        $this->getChildBlock('form')->setData('action', $this->getSaveUrl());
        return $this->getChildHtml('form');
    }

    /**
     * @return string
     */
    public function getFormInitScripts()
    {
        if (!empty($this->_formInitScripts) && is_array($this->_formInitScripts)) {
            return '<script>' . implode("\n", $this->_formInitScripts) . '</script>';
        }
        return '';
    }

    /**
     * @return string
     */
    public function getFormScripts()
    {
        if (!empty($this->_formScripts) && is_array($this->_formScripts)) {
            return '<script>' . implode("\n", $this->_formScripts) . '</script>';
        }
        return '';
    }

    /**
     * @return string
     */
    public function getHeaderWidth()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-' . strtr($this->_controller, '_', '-');
    }

    /**
     * @return string
     */
    public function getHeaderHtml()
    {
        return '<h3 class="' . $this->getHeaderCssClass() . '">' . $this->getHeaderText() . '</h3>';
    }

    /**
     * Set data object and pass it to form
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function setDataObject($object)
    {
        $this->getChildBlock('form')->setDataObject($object);
        return $this->setData('data_object', $object);
    }
}
