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
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Category extends GenericForm implements TabInterface
{

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        $this->_categoryRepository = $categoryRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var $model ProductRequest */
        $model = $this->_coreRegistry->registry('vendor_product_request');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('productrequest_');

        $isCollapsable = true;

        // Initialize product object as form property to use it during elements generation
        $form->setDataObject($model);

        $fieldset = $form->addFieldset(
            'group-fields-',
            ['class' => 'user-defined', 'legend' => __('Category'), 'collapsable' => true]
        );

        $model->setData(
            'category_id',
            $this->getCategoryPath($model->getCategoryId())
        );
        $fieldset->addField(
            'category_id',
            'label',
            [
                    'name' => 'category_id',
                    'label' => __('Category'),
                    'class' => 'category'
                ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Category Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Category Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function getCategoryPath($id, $storeId = 0)
    {
        $string = '';
        $cnt = 0;
        $pathIds = $this->_categoryRepository->get($id, $storeId)->getPath();
        $path = explode('/', $pathIds);

        foreach ($path as $pathId) {
            $cnt++;
            $sufix = (count($path) == $cnt) ? '' : ' > ';
            $string.= $this->_categoryRepository->get($pathId)->getName() . $sufix;
        }

        return $string;
    }
}
