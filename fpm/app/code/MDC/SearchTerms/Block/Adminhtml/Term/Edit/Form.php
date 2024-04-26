<?php

namespace MDC\SearchTerms\Block\Adminhtml\Term\Edit;

class Form extends \Magento\Search\Block\Adminhtml\Term\Edit\Form
{


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
    
        parent::__construct($context, $registry, $formFactory,$systemStore,$data);
    }  

    protected function _construct()
    {
        parent::_construct();      
    }
    
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_catalog_search');
        /* @var $model \Magento\Search\Model\Query */

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        $yesno = [['value' => 0, 'label' => __('No')], ['value' => 1, 'label' => __('Yes')]];

        $redirectType = [['value' => 'category', 'label' => __('Category')], ['value' => 'product', 'label' => __('Product')], ['value' => 'cms', 'label' => __('CMS')]];

        if ($model->getId()) {
            $fieldset->addField('query_id', 'hidden', ['name' => 'query_id']);
        }

        $fieldset->addField(
            'query_text',
            'text',
            [
                'name' => 'query_text',
                'label' => __('Search Query'),
                'title' => __('Search Query'),
                'required' => true
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'select',
                [
                    'name' => 'store_id',
                    'label' => __('Store'),
                    'title' => __('Store'),
                    'values' => $this->_systemStore->getStoreValuesForForm(true, false),
                    'required' => true
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField('store_id', 'hidden', ['name' => 'store_id']);
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        if ($model->getId()) {
            $fieldset->addField(
                'num_results',
                'text',
                [
                    'name' => 'num_results',
                    'label' => __('Number of results'),
                    'title' => __('Number of results (For the last time placed)'),
                    'note' => __('For the last time placed.'),
                    'required' => true,
                    'class' => 'required-entry validate-digits validate-zero-or-greater'
                ]
            );

            $fieldset->addField(
                'popularity',
                'text',
                [
                    'name' => 'popularity',
                    'label' => __('Number of Uses'),
                    'title' => __('Number of Uses'),
                    'required' => true,
                    'class' => 'required-entry validate-digits validate-zero-or-greater'
                ]
            );
        }

        $fieldset->addField(
            'redirect',
            'text',
            [
                'name' => 'redirect',
                'label' => __('Redirect URL'),
                'title' => __('Redirect URL'),
                'class' => 'validate-url',
                'note' => __('ex. http://domain.com')
            ]
        );

        $fieldset->addField(
            'redirection_type',
            'select',
            [
                'name' => 'redirection_type',
                'label' => __('Type'),
                'title' => __('Type'),
                'values' => $redirectType
            ]
        );

        $fieldset->addField(
            'redirect_id',
            'text',
            [
                'name' => 'redirect_id',
                'label' => __('ID'),
                'title' => __('ID'),                
                'note' => __('{product ID} or {category ID} or {CMS ID}')
            ]
        );

        $fieldset->addField(
            'display_in_terms',
            'select',
            [
                'name' => 'display_in_terms',
                'label' => __('Display in Suggested Terms'),
                'title' => __('Display in Suggested Terms'),
                'values' => $yesno
            ]
        );
              
         
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        // return parent::_prepareForm();
        return \Magento\Backend\Block\Widget\Form\Generic::_prepareForm();

    }
}
