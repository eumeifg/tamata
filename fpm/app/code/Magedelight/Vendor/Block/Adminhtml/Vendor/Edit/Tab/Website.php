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
namespace Magedelight\Vendor\Block\Adminhtml\Vendor\Edit\Tab;

use Magedelight\Vendor\Controller\RegistryConstants;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Website extends Generic implements TabInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Prepare label for tab
     * @return \Magento\Framework\Phrase
     */

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);

        $form->setHtmlIdPrefix('vendor_');
        $form->setFieldNameSuffix('vendor');

        $fieldset = $form->addFieldset(
            'meta_fieldset',
            ['legend' => __('Website Details'), 'class' => 'fieldset-wide']
        );

        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        foreach ($this->storeManager->getWebsites() as $website) {
            $options[] = [
                'label' => str_repeat($nonEscapableNbspChar, 4) . $website->getName(),
                'value' => $website->getId(),
            ];
        }

        if ($vendor->getId()) {
            $collection = $this->vendorFactory->create()->getCollection()
                    ->addFieldToFilter('email', $vendor->getEmail())
                    ->addFieldToSelect(['website_id']);
            $collection->getSelect()
                ->join('store_website', 'store_website.website_id = main_table.website_id', ['*']);

            $sites = [];
            $registeredWebsiteIds = [];
            foreach ($collection as $site) {
                $registeredWebsiteIds[] = $site->getWebsiteId();
                $sites[] = $site->getName();
            }

            $text = __(
                '%1 Vendor with email %4 is registered on following sites (%2).%3 Please select the site in which you want the vendor to get registered.',
                '-',
                '<strong>' . implode(', ', $sites) . '</strong>',
                '<br><br>- ',
                '<u>' . $vendor->getEmail() . '</u>'
            );
            $text .= '<br/><br/>';
            $text .= '-&nbsp;' . __(
                'Vendor account with same email can only be created from this section and cannot be removed from here.'
            );
            $text .= '<br/><br/>';
            $text .= '-&nbsp;' . __(
                'To create vendor with different email, please use %1 button in the approved vendor grid.',
                '<strong>' . __('Add New Vendor') . '</strong>'
            );

            $fieldset->addField(
                'registered_website_details',
                'note',
                [
                    'name' => 'registered_website_details',
                    'text' =>  $text,
                    'label' =>  __('Note'),
                ]
            );
        }

        $fieldset->addField(
            'website_ids',
            'multiselect',
            [
            'name' => 'website_ids[]',
            'label' => __('Select Websites'),
            'title' => __('Select Websites'),
            'values' => $options,
            'required' => true
            ]
        );

        if ($vendor->getId()) {
            $vendor->setWebsiteIds(implode(',', $registeredWebsiteIds));
        } else {
            /* Set website selected if single website.*/
            if (count(array_column($options, 'value')) == 1) {
                $vendor->setWebsiteIds(implode(',', array_column($options, 'value')));
            }
        }
        $data = $vendor->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Website Details');
    }

    /**
     * Prepare title for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Website Details');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);

        if ($vendor->getId() && $vendor->getStatus() != \Magedelight\Vendor\Model\Source\Status::VENDOR_STATUS_ACTIVE) {
            return false;
        }

        if (!$vendor->getIsUser()) {
            return true;
        }
        return false;
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
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
