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
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest\Edit;

use Magento\Framework\Translate\InlineInterface;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var InlineInterface
     */
    private $_translateInline;

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var string
     */
    protected $_attributeTabBlock = \Magedelight\Catalog\Block\Adminhtml\ProductRequest\Edit\Tab\Attributes::class;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        InlineInterface $translateInline,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_translateInline = $translateInline;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_request_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Product Request Information'));
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareLayout()
    {
        /** @var $model \Magedelight\Catalog\Model\ProductRequest */
        $model = $this->_coreRegistry->registry('vendor_product_request');

        if ($model->getHasVariants()) {
            $offerTabLabel = __('General Information');
        } else {
            $offerTabLabel = __('Offer Information');
        }

        $this->addTab(
            'offer_information',
            [
                'label' => $offerTabLabel,
                'content' => $this->_translateHtml(
                    $this->getLayout()->createBlock(
                        \Magedelight\Catalog\Block\Adminhtml\ProductRequest\Edit\Tab\Offer::class
                    )->toHtml()
                ),
                'group_code' => 'offer_information'
            ]
        );

        if ((!$model->getData('marketplace_product_id', false)) || $model->getData('is_requested_for_edit', false)) {
            $attributeSetId = $model->getData('attribute_set_id');
            $attributes = $model->getProductAttributes($attributeSetId);
            $attributeGroups = [];
            $excludeAttributes = $model->getExcludeAttributeList();

            foreach ($attributes as $key => $attribute) {
                $attrs[] =  $attribute->getAttributeCode();
                if ($attribute->getAttributeCode() == 'url_key') {
                    /* Allow url key to be modified as per magento standards in case duplicate product name found.*/
                    unset($excludeAttributes[$attribute->getAttributeId()]);
                }

                if (!$attribute->getIsVisible() ||
                    in_array($attribute->getAttributeId(), $excludeAttributes)
                    && ($attribute->getAttributeCode() != 'url_key')
                ) {
                    /* Allow url key to be modified as per magento standards in case duplicate product name found.*/
                    unset($attributes[$key]);
                } elseif ($attribute->getFrontendInput() == 'select'
                    && $attribute->getIsUserDefined()
                    && $attribute->getIsGlobal()

                ) {
                    if ($this->isAttributeApplicable($attribute) && !$model->getHasVariants()) {
                        $attributeGroups['variants_information'][$key] = $attribute;
                    }
                } elseif ($attribute->getIsRequired() || $attribute->getAttributeCode() == 'url_key') {
                    /* Allow url key to be modified as per magento standards in case duplicate product name found.*/
                    $attributeGroups['vital_information'][$key] = $attribute;
                } else {
                    if (!in_array($key, ['quantity_and_stock_status', 'media_gallery'])) {
                        $attributeGroups['more_information'][$key] = $attribute;
                    }
                }
            }

            if (!empty($attributeGroups)) {
                $tabAttributesBlock = $this->getLayout()->createBlock(
                    $this->getAttributeTabBlock(),
                    $this->getNameInLayout() . '_attributes_tab'
                );
                foreach ($attributeGroups as $group => $groupAttributes) {
                    if (!empty($groupAttributes)) {
                        $tabData = [
                            'label' => __(ucwords(str_replace('_', ' ', $group))),
                            'content' => $this->_translateHtml(
                                $tabAttributesBlock->setGroup($group)->setGroupAttributes($groupAttributes)->toHtml()
                            ),
                            'class' => 'user-defined',
                            'group_code' => $group,
                        ];
                        $this->addTab($group, $tabData);
                    }
                }
            }
        }

        if ($model->getHasVariants()) {
            $this->addTab(
                'variants',
                [
                'label' => __('Variants'),
                'content' => $this->_translateHtml(
                    $this->getLayout()->createBlock(
                        \Magedelight\Catalog\Block\Adminhtml\ProductRequest\Edit\Tab\Variants::class
                    )->toHtml()
                ),
                'group_code' => 'variants'
                ]
            );
        }

        $this->addTab(
            'category',
            [
                'label' => __('Category'),
                'content' => $this->_translateHtml(
                    $this->getLayout()->createBlock(
                        \Magedelight\Catalog\Block\Adminhtml\ProductRequest\Edit\Tab\Category::class
                    )->toHtml()
                ),
                'group_code' => 'category'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->_translateInline->processResponseBody($html);
        return $html;
    }

    /**
     * Getting attribute block name for tabs
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
        return $this->_attributeTabBlock;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return bool
     */
    public function isAttributeApplicable($attribute)
    {
        $types = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
        ];
        return !$attribute->getApplyTo() || count(array_diff($types, $attribute->getApplyTo())) === 0;
    }
}
