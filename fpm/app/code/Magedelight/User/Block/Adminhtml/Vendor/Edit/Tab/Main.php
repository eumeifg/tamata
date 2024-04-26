<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Block\Adminhtml\Vendor\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magedelight\Vendor\Controller\RegistryConstants;
use Magedelight\Vendor\Model\Source\Status;

class Main extends Generic implements TabInterface
{

    /**
     * @var \Magedelight\Vendor\Model\Source\Status
     */
    protected $statusOption;

    /**
     * @var \Magedelight\Theme\Model\Source\DefaultCountry
     */
    protected $defaultCountry;

    /**
     * @var \Magedelight\Theme\Model\Source\Region
     */
    protected $region;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    
    /**
     * @var \Magento\Directory\Block\Data
     */
    protected $directoryHelper;
    
    protected $backendSession;
    
     /**
      * @var \Magento\Directory\Model\AllowedCountriesFactory
      */
    protected $allowedCountriesFactory;
    
    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;
    
    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magedelight\Theme\Model\Source\Region $region
     * @param \Magedelight\Theme\Model\Source\DefaultCountry $defaultCountry
     * @param Status $statusOption
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Directory\Model\AllowedCountriesFactory $allowedCountriesFactory
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magedelight\Theme\Model\Source\Region $region,
        \Magedelight\Theme\Model\Source\DefaultCountry $defaultCountry,
        \Magedelight\Vendor\Model\Source\Status $statusOption,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Directory\Model\AllowedCountriesFactory $allowedCountriesFactory,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        $this->region = $region;
        $this->defaultCountry = $defaultCountry;
        $this->statusOption = $statusOption;
        $this->directoryHelper = $directoryHelper;
        $this->backendSession = $context->getBackendSession();
        $this->allowedCountriesFactory = $allowedCountriesFactory;
        $this->vendorHelper = $vendorHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);

        if ($vendor->getId()) {
            $form->addField('vendor_id', 'hidden', ['name' => 'vendor_id']);
        }

        $form->setHtmlIdPrefix('vendor_');
        $form->setFieldNameSuffix('vendor');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Profile Details')]);

        $fieldset->addType('image', 'Magedelight\Vendor\Block\Adminhtml\Helper\Image');

        $fieldset->addField(
            'name',
            'text',
            [
            'name' => 'name',
            'label' => __('Name'),
            'title' => __('Name'),
            'class' => 'validate-alpha-with-spaces-name',
            'required' => true,
            'autofocus' => true,
            ]
        );

        if ($vendor->getId()) {
            $vendor->setEmailId($vendor->getEmail());
            $fieldset->addField(
                'email_id',
                'text',
                [
                'name' => 'email_id',
                'label' => __('User Email/ Username'),
                'title' => __('User Email/ Username'),
                'required' => true,
                'disabled' =>'disabled',
                'class' => 'validate-email disabled',
                ]
            );
            $fieldset->addField(
                'email',
                'hidden',
                [
                'name' => 'email',
                'class' => 'validate-email disable',
                ]
            );
        } else {
            $fieldset->addField(
                'email',
                'text',
                [
                'name' => 'email',
                'label' => __('Vendor Email'),
                'title' => __('Vendor Email'),
                'required' => true,
                'class' => 'validate-email',
                ]
            );
        }

        $fieldset->addField(
            'status',
            'select',
            [
            'name' => 'status',
            'label' => __('Profile Status'),
            'title' => __('Profile Status'),
            'values' => $this->statusOption->toOptionArray(),
            'required' => true
            ]
        );

        if (!$vendor->getId() && $sessionData) {
            $vendor->setData('mobile', '+'.$sessionData['vendor']['country_code'].$sessionData['vendor']['mobile']);
        }
        $form->setValues($vendor->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        /* get the current form as html content. */
        $html = parent::getFormHtml();

        return $html;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Profile Details');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Vendor Information');
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
}
