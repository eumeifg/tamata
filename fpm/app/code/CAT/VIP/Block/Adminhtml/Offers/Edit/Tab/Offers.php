<?php
namespace CAT\VIP\Block\Adminhtml\Offers\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;

class Offers extends Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        $this->_websiteFactory = $websiteFactory;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('offers_');
        $isElementDisabled = false;
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Offers')]);

        

        $fieldset->addField(
            'vendor_offers',
            'file',
            [
                'name' => 'vip_offers',
                'label' => __('Choose a csv file'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'sample',
            'link',
            [
            'name'      => __('Download a sample csv file'),
            'href'      => 'javascript:void(0);',
            'value'     => __('Download a sample csv file'),
            ]
        );

        $text = '<b>' . 'product_id' . '</b>' . ' : Enter product id as seen in core product. ';
        $text .= 'Refer Catalog > Products to get ID.';
        $text .= '<br/><br/>';
        $text .= '<b>' . 'vendor_id' . '</b>' . ' : ';
        $text .= 'Enter vendor_id and make sure it is same as selected in vendor field.';
        $text .= ' Refer ROCKET BAZAAR > VENDORS > APPROVED/ACTIVE to get vendor id.';
        $text .= '<br/><br/>';
        $text .= '<b>' . 'type' . '</b>' . ' : Enter Type "Fixed" OR "Percentage".';
        $text .= '<br/><br/>';
        $text .= '<b>' . 'customer_group' . '</b>' . ' : Cutomer Groups Id will added with comma separated Like 1,23,50';
        $text .= '<br/><br/>';
        $text .= '<br/>';

        $fieldset->addField(
            'registered_website_details',
            'note',
            [
                'name' => 'registered_website_details',
                'text' =>  $text,
                'label' =>  __('Guidelines'),
            ]
        );

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
        return __('Add Offers');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Add Offers');
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
     * @return string
     */
    public function getVendorListUrl()
    {
        //return $this->getUrl('vendoroffers/index/vendorList');
    }
}
