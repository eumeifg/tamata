<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Adminhtml\Review\Vendorrating\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class Vendorrating extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->customerRepository = $customerRepository;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('vendorrating');

        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Vendor Rating')]);

        if ($model->getId()) {
            $fieldset->addField('vendor_rating_id', 'hidden', ['name' => 'vendor_rating_id']);
        }

        try {
            $customer = $this->customerRepository->getById($model->getCustomerId());
            $customerText = __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                $this->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'review']),
                $this->escapeHtml($customer->getFirstname()),
                $this->escapeHtml($customer->getLastname()),
                $this->escapeHtml($customer->getEmail())
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customerText = ($customer->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID)
                ? __('Administrator') : __('Guest');
        }

        $fieldset->addField('customer', 'note', ['label' => __('Author'), 'text' => $customerText]);

        $fieldset->addField(
            'summary_rating',
            'note',
            [
                'label' => __('Summary Rating'),
                'text' => '<div>' . $this->getLayout()->createBlock(
                    \Magedelight\Vendor\Block\Adminhtml\Review\Vendorrating\Detailed::class
                )->toHtml() . '</div>'
            ]
        );

        $fieldset->addField(
            'detailed_rating',
            'note',
            [
                'label' => __('Detailed Rating'),
                'text' => '<div id="rating_detail">' . $this->getLayout()->createBlock(
                    \Magedelight\Vendor\Block\Adminhtml\Review\Vendorrating\Rating::class
                )->toHtml() . '</div>'
            ]
        );

        $fieldset->addField(
            'is_shared',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_shared',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'comment',
            'textarea',
            [
                'name' => 'comment',
                'label' => __('Review'),
                'title' => __('Review'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'style' => 'height:24em;'
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Vendor Rating');
    }

    public function getTabTitle()
    {
        return __('Vendor Rating');
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
        return $this->_authorization->isAllowed('Magedelight_Vendor::vendorrating');
    }
}
