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
namespace Magedelight\Catalog\Block\Sellerhtml\ApprovedProduct;

class Edit extends \Magedelight\Catalog\Block\Sellerhtml\AbstractProduct
{

    /**
     *
     * @var  \Magedelight\Catalog\Model\ProductRequest
     */
    protected $vendorProduct;

    /**
     * @return null | \Magedelight\Catalog\Model\ProductRequest
     */
    public function getCurrentRequest()
    {
        $storeId = ($this->_request->getParam('store')) ? $this->_request->getParam('store') : 'default';
        if (!$this->vendorProduct) {
            $this->vendorProduct = $this->coreRegistry->registry('vendor_current_product_core');
            if ($this->vendorProduct) {
                $attributesJSON = $this->vendorProduct->getData('attributes');
                if (!($attributesJSON === null) && is_string($attributesJSON)) {
                    $attrValues = json_decode($attributesJSON, true);
                    foreach ($attrValues[$storeId] as $key => $value) {
                        $this->vendorProduct->setData($key, $value);
                    }
                }
                return $this->vendorProduct;
            } else {
                return false;
            }
        }
        return $this->vendorProduct;
    }

    /**
     * @return string
     */
    public function getEditPostActionUrl()
    {
        $queryParams = [
            'p' => $this->getRequest()->getParam('p', 1),
            'sfrm' => $this->getRequest()->getParam('sfrm', 'l'),
            'limit' => $this->getRequest()->getParam('limit', 10),
        ];
        return $this->getUrl('rbcatalog/product/editpost/', ['_query' => $queryParams]);
    }

    /**
     * @return \Magedelight\Catalog\Model\Product
     */
    public function getVendorProduct()
    {
        $vendorProductId = $this->getRequest()->getParam('id');
        $vendorProduct = $this->_vendorProductFactory->create()->load($vendorProductId);
        return $vendorProduct;
    }

    /**
     * @return mixed
     */
    public function isView()
    {
        return $this->getRequest()->getParam('view');
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        if ($this->isRequestResubmitted()) {
            return $this->coreRegistry->registry('vendor_current_product_core');
        } else {
            $prodId = $this->getRequest()->getParam('pid', false);
        }
        if ($prodId) {
            return $this->productRepository->getById(
                $prodId,
                false,
                $this->_storeManager->getStore()->getId()
            );
        }
    }

    /**
     * @param $attribute
     * @return string|null
     * @throws \Exception
     */
    protected function setFrontendInputToHtml($attribute)
    {
        $html = null;

        if ($this->isRequestResubmitted() || $this->getCurrentRequest() != null) {
            $attribute->setData('current_value', $this->getAttributeValue($attribute->getAttributeCode()));
        }

        switch ($attribute->getFrontendInput()) {
            case 'textarea':
                $html = $this->renderTextarea($attribute);
                break;
            case 'text':
                $html = $this->renderText($attribute);
                break;
            case 'weight':
                $html = $this->renderWeight($attribute);
                break;
            case 'boolean':
            case 'select':
                $html = $this->renderDropdown($attribute);
                break;
            case 'multiselect':
                $html = $this->renderDropdown($attribute, true);
                break;
            case 'date':
                $html = $this->renderDate($attribute);
                break;
            case 'image':
                $html = $this->renderImage($attribute);
                break;
        }

        return $html;
    }

    /**
     * @param $attribute
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function renderImage($attribute)
    {
        $tooltip = $this->getTooltipText($attribute);
        if ($tooltip == '') {
            $tooltip = $attribute->getStoreLabel();
        }
        $optionalClasses = '';
        $numericAttributeCodes = ['product_length', 'product_width', 'product_height', 'return_period'];
        if (in_array($attribute->getAttributeCode(), $numericAttributeCodes)) {
            $optionalClasses = 'validate-not-negative-number';
        }
        $requiredClass = $attribute->getIsRequired() ? ' required-entry' : '';
        $html = '<input class="input-text' . $requiredClass . ' ' . $optionalClasses . '"';
        $html .= ' type="file"';
        $html .= ' id="' . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . '"';
        $html .= ' name="' . $attribute->getAttributeCode() . '"';
        $html .= ' value="' . htmlspecialchars($attribute->getData('current_value')) . '"';
        $html .= ' accept="image/jpeg, image/jpg, image/png"';
        if ($attribute->getIsRequired()) {
            $html .= ' data-validate="{required:true}"';
        }
        if ($this->isView()) {
            $html .= ' disabled ';
        }
        $html .= '"/>';
        if ($attribute->getData('current_value')) {
            $html .= '<input type="hidden" name="' . $attribute->getAttributeCode() . '_value' . '" value="'
            . htmlspecialchars($attribute->getData('current_value')) . '"/>';
        }
        if ($attribute->getData('current_value')) {
            $media_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $html .= '<img src="' . $media_url . $attribute->getData('current_value') . '" width="50" height="50" />';
        }

        $html .= '<div class="field-tooltip toggle">';
        $html .= '<span class="field-tooltip-action action-help" tabindex="0"></span>';
        $html .= '<div class="field-tooltip-content"><span>' . $tooltip . '</span></div></div>';
        return $html;
    }

    /**
     * @param $attribute
     * @param bool $multiselect
     * @param bool $hidden
     * @param bool $isRequired
     * @return string
     * @throws \Exception
     */
    public function renderDropdown($attribute, $multiselect = false, $hidden = false, $isRequired = true)
    {
        $tooltip = $this->getTooltipText($attribute);
        if ($tooltip == '') {
            $tooltip = $attribute->getStoreLabel();
        }
        $hidden = ($hidden) ? 'no-display' : null;
        $multiple = $multiselect ? ' multiple' : '';
        $html = '<select class="select' . $multiple . ' ' . $hidden . '"';
        $html .= ' id="' . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . '"';
        $html .= ' name="' . $this->getFieldNamePrefix() .
        $attribute->getAttributeCode() . $this->getFieldNameSuffix() . '"';
        if ($attribute->getIsRequired()) {
            $html .= ' data-validate="{required:true}"';
        }
        if ($this->isView()) {
            $html .= ' disabled ';
        }
        $html .= $multiple;

        $html .= '>';

        try {
            if ($attribute->getOptions()) {
                foreach ($attribute->getOptions() as $option) {
                    $selected = '';
                    if (is_array(explode(',', $attribute->getData('current_value'))) &&
                        in_array($option['value'], explode(',', $attribute->getData('current_value')))) {
                        $selected = ' selected="selected"';
                    } else {
                        if ($attribute->getData('current_value')) {
                            if ($attribute->getData('current_value') == $option['value']) {
                                $selected = ' selected="selected"';
                            }
                        } elseif ($option['value'] == $attribute->getDefaultValue()) {
                            $selected = ' selected="selected"';
                        }
                    }
                    $html .= '<option value="' . $option['value'] . '"' . $selected . '>';
                    $html .= $option['label'] . '</option>';
                }
            }
        } catch (\Exception $exc) {
            throw new \Exception($exc->getTraceAsString());
        }
        $html .= ' </select>';

        $html .= '<div class="field-tooltip toggle">';
        $html .= '<span class="field-tooltip-action action-help" tabindex="0"></span>';
        $html .= '<div class="field-tooltip-content"><span>' . $tooltip . '</span></div></div>';

        return $html;
    }

    /**
     * @param $attribute
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function renderText($attribute)
    {
        $tooltip = $this->getTooltipText($attribute);
        if ($tooltip == '') {
            $tooltip = $attribute->getStoreLabel();
        }
        $requiredClass = $attribute->getIsRequired() ? ' required-entry' : '';
        $html = '<input class="input-text' . $requiredClass . '"';
        $html .= ' type="text"';
        $html .= ' id="' . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . '"';
        $html .= ' name="' . $this->getFieldNamePrefix() .
        $attribute->getAttributeCode() . $this->getFieldNameSuffix() . '"';
        $html .= ' value="' . $this->getProduct()->getData($attribute->getAttributeCode()) . '"';
        if ($attribute->getIsRequired()) {
            $html .= ' data-validate="{required:true}"';
        }
        if ($this->isView()) {
            $html .= ' disabled ';
        }
        $html .= ' placeholder="' . $attribute->getLabel() . '"/>';

        $html .= '<div class="field-tooltip toggle">';
        $html .= '<span class="field-tooltip-action action-help" tabindex="0"></span>';
        $html .= '<div class="field-tooltip-content"><span>' . $tooltip . '</span></div></div>';

        return $html;
    }

    /**
     * @param $attribute
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function renderWeight($attribute)
    {
        $tooltip = $this->getTooltipText($attribute);
        if ($tooltip == '') {
            $tooltip = $attribute->getStoreLabel();
        }

        $requiredClass = $attribute->getIsRequired() ? ' required-entry' : '';
        $html = '<input class="input-text' . $requiredClass . '"';
        $html .= ' type="text"';
        $html .= ' id="' . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . '"';
        $html .= ' name="' . $this->getFieldNamePrefix() .
        $attribute->getAttributeCode() . $this->getFieldNameSuffix() . '"';
        $html .= ' value="' . $this->getProduct()->getData($attribute->getAttributeCode()) . '"';
        if ($attribute->getIsRequired()) {
            $html .= ' data-validate="{required:true}"';
        }
        if ($this->isView()) {
            $html .= ' disabled ';
        }
        $html .= ' placeholder="' . $attribute->getLabel() . '"/>';
        $html .= '<span class="vendor-field-suffix">' .
        $this->directoryHelper->getWeightUnit() .
            '</span>';

        $html .= '<div class="field-tooltip toggle">';
        $html .= '<span class="field-tooltip-action action-help" tabindex="0"></span>';
        $html .= '<div class="field-tooltip-content"><span>' . $tooltip . '</span></div></div>';

        return $html;
    }

    /**
     * @param $attribute
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function renderTextarea($attribute)
    {
        $tooltip = $this->getTooltipText($attribute);
        if ($tooltip == '') {
            $tooltip = $attribute->getStoreLabel();
        }

        $requiredClass = $attribute->getIsRequired() ? ' required-entry' : '';
        $html = '<textarea class="input-text' . $requiredClass . '"';
        $html .= ' id="' . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . '"';
        $html .= ' name="' . $this->getFieldNamePrefix() .
        $attribute->getAttributeCode() . $this->getFieldNameSuffix() . '" ';
        if ($attribute->getIsRequired()) {
            $html .= ' data-validate="{required:true}" ';
        }
        if ($this->isView()) {
            $html .= ' disabled ';
        }
        $html .= 'placeholder="' . $attribute->getLabel() . '">' . $attribute->getData('current_value') . '</textarea>';

        $html .= '<div class="field-tooltip toggle">';
        $html .= '<span class="field-tooltip-action action-help" tabindex="0"></span>';
        $html .= '<div class="field-tooltip-content"><span>' . $tooltip . '</span></div></div>';

        return $html;
    }

    /**
     * @param $attribute
     * @return string
     */
    protected function renderDate($attribute)
    {
        $requiredClass = $attribute->getIsRequired() ? ' required-entry' : '';
        $html = '<input class="input-text' . $requiredClass . '"';
        $html .= ' type="text"';
        $html .= ' id="' . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . '"';
        $html .= ' name="' . $this->getFieldNamePrefix() .
        $attribute->getAttributeCode() . $this->getFieldNameSuffix() . '"';
        $html .= ' value="' . htmlspecialchars($attribute->getData('current_value')) . '"';
        if ($attribute->getIsRequired()) {
            $html .= ' data-validate="{required:true}"';
        }
        if ($this->isView()) {
            $html .= ' disabled ';
        }
        $html .= ' placeholder="MM/DD/YYYY"/>';

        return $html;
    }

    /**
     * Get Eav Attributes of product entity filter by Attribute Set.
     * And assign attributes into vital, additional & variants array.
     * Unset unnecessary collection of attributes.
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributes()
    {
        $attributeSetId = ($this->getCategoryAttributeSetId()) ?: self::DEFAULT_ATTRIBUTE_SET_ID;

        $marketplaceProductId = $this->getVendorProduct()->getMarketplaceProductId();

        /**
         * if product found parentId then consider it is child/associsted product.
         * then load super product data.
         */
        if ($this->getVendorProduct()->getParentId()) {
            $marketplaceProductId = $this->getVendorProduct()->getParentId();
        }

        $product = $this->getProduct();

        $attributes = $product->getAttributes();

        $excludeAttributes = $this->getExcludeAttributeList();

        foreach ($attributes as $key => $attribute) {
            if (!$attribute->getIsVisible() || in_array($attribute->getAttributeId(), $excludeAttributes)) {
                unset($attributes[$key]);
            } elseif ($attribute->getFrontendInput() == 'select' &&
                $attribute->getIsUserDefined() &&
                $attribute->isScopeGlobal()) {
                $this->variantAttributes[$key] = $attribute;
            } elseif ($attribute->getIsRequired()) {
                $this->vitalAttributes[$key] = $attribute;
            } else {
                $this->additionalAttributes[$key] = $attribute;
            }
        }
    }

    /**
     * @param $attributes
     * @return string
     * @throws \Exception
     */
    public function getHtml($attributes)
    {
        $suffix = 'vendor-';
        $html = '';
        try {
            foreach ($attributes as $attribute) {
                if (!($attribute->getFrontendInput() === null)) {
                    $required = $attribute->getIsRequired() ? ' required' : '';
                    $html .= '<div class="field '
                    . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . $required . '">';

                    $html .= '<label class="label" for="'
                    . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . '">';
                    $html .= '<span>' . __($attribute->getStoreLabel()) . '</span>';
                    $html .= '</label>';

                    $html .= '<div class="control _with-tooltip">';
                    $html .= $this->setFrontendInputToHtml($attribute);
                    $html .= '</div>';

                    $html .= '</div>';
                }
            }

            return $html;
        } catch (\Exception $exc) {
            throw new \Exception($exc->getMessage());
        }
    }

    /**
     *
     * @return render attributes into html
     * @throws \Exception
     */
    public function getAttributesByAttributeSet()
    {
        $attrAll = $this->coreRegistry->registry('current_category_attributes');
        return $this->getHtml($attrAll);
    }

    /**
     * @return string
     */
    public function getBreadCrumb()
    {
        $html = '<div class="breadcrumbs">';
        $html .= '<div class="page-main">';
        $html .= '<ul class="items">';
        $html .= '<li class="item home"><a title="Go to Home Page" href="#">Home</a></li>';

        $catnames = [];
        foreach ($this->getLoadedCategory()->getParentCategories() as $parent) {
            if ($this->escapeHtml($parent->getName()) == $this->escapeHtml($this->getLoadedCategory()->getName())) {
                $html .= '<li class="item"><strong>' . $parent->getName() . '</strong></li>';
            } else {
                $html .= '<li class="item">' . $parent->getName() . '</li>';
            }
        }
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @return string
     */
    public function getUniqueskuPostActionUrl()
    {
        return $this->getUrl('rbcatalog/product/uniquesku');
    }
}
