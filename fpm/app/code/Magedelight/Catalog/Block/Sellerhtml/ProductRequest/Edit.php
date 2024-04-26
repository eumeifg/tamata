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
namespace Magedelight\Catalog\Block\Sellerhtml\ProductRequest;

class Edit extends \Magedelight\Catalog\Block\Sellerhtml\AbstractProduct
{
    /**
     *
     * @var  \Magedelight\Catalog\Model\ProductRequest
     */
    protected $productRequest;

    /**
     * @return null | \Magedelight\Catalog\Model\ProductRequest
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentRequest()
    {
        $storeId = ($this->_request->getParam('store')) ? $this->_request->getParam('store') : 0;
        $storeCode = ($storeId == 'default') ? $this->_storeManager->getStore()->getId() : $storeId;
        $websiteId = $this->_storeManager->getStore($storeCode)->getWebsiteId();
        if (!$this->productRequest) {
            $this->productRequest = $this->coreRegistry->registry('vendor_current_product_request');

            if ($this->productRequest) {
                $collection = $this->_productRequestStoreCollectionFactory->create();
                $productRequestStore = $collection->addFieldToFilter(
                    'product_request_id',
                    $this->productRequest->getProductRequestId()
                )->addFieldToFilter('store_id', $storeId)
                    ->addFieldToFilter('website_id', $this->_storeManager->getStore()->getWebsiteId())
                    ->getFirstItem();
                /*
                Checked here that store data exists or not. If it does not exist then we get the data from
                Main Vendor Product data for that store view and if that also does not exist then we
                show the default store view data.
                 */
                if ($productRequestStore->hasData('row_id')) {
                    $attributesJSON = $productRequestStore->getData('attributes');
                    if (is_string($attributesJSON)) {
                        $attrValues = json_decode($attributesJSON, true);
                        foreach ($attrValues as $key => $value) {
                            $this->productRequest->setData($key, $value);
                        }
                    }

                    $storeValues = $productRequestStore->getData();
                    foreach ($storeValues as $key => $value) {
                        $this->productRequest->setData($key, $value);
                    }
                } else {
                    $mainProduct = $this->_vendorProductFactory->create()->getCollection()->addFieldToFilter('main_table.vendor_product_id', $this->productRequest->getVendorProductId())->addFieldToFilter('cpev.store_id', $storeId)->getFirstItem();
                    if (!($mainProduct->getData())) {
                        $mainProduct = $this->_vendorProductFactory->create()->getCollection()->addFieldToFilter('main_table.vendor_product_id', $this->productRequest->getVendorProductId())->getFirstItem();
                    }
                    $this->productRequest->setData('name', $mainProduct->getData('product_name'));
                }

                $collection = $this->_productRequestWebsiteCollectionFactory->create();
                $productRequestWebsite = $collection->addFieldToFilter(
                    'product_request_id',
                    $this->productRequest->getProductRequestId()
                )->addFieldToFilter(
                    'website_id',
                    $this->_storeManager->getStore()->getWebsiteId()
                )->getFirstItem();
                $websiteValues = $productRequestWebsite->getData();
                foreach ($websiteValues as $key => $value) {
                    $this->productRequest->setData($key, $value);
                }
            } else {
                return null;
            }
        }
        return $this->productRequest;
    }

    /**
     * @return string
     */
    public function getCreatePostActionUrl()
    {
        $queryParams = [
            'p' => $this->getRequest()->getParam('p', 1),
            'sfrm' => $this->getRequest()->getParam('sfrm', 'l'),
            'limit' => $this->getRequest()->getParam('limit', 10),
        ];
        return $this->getUrl('rbcatalog/product/createpost', ['_query' => $queryParams]);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        if ($this->isRequestResubmitted()) {
            if ($this->getCurrentRequest()->getIsRequestedForEdit()) {
                $prodId = null;
            } else {
                $prodId = $this->getAttributeValue('marketplace_product_id');
            }
        } else {
            $prodId = $this->getRequest()->getParam('pid', false);
        }
        $product = $this->productFactory->create();
        if ($prodId) {
            return $product->load($prodId);
        }
        return $product;
    }

    /**
     * @return mixed
     */
    public function isView()
    {
        return $this->getRequest()->getParam('view');
    }

    /**
     * @param $attribute
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
        $html .= '"/>';
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function renderDropdown($attribute, $multiselect = false, $hidden = false, $isRequired = true)
    {
        $tooltip = $this->getTooltipText($attribute);
        if ($tooltip == '') {
            $tooltip = $attribute->getStoreLabel();
        }
        $hidden = ($hidden) ? 'no-display' : null;
        $multiple = '';
        if ($multiselect) {
            $multiple = ' multiple';
            $suffix = $this->getFieldNameSuffix() . '[]';
        } else {
            $suffix = $this->getFieldNameSuffix();
        }
        $html = '<select class="select' . $multiple . ' ' . $hidden . '"';
        $html .= ' id="' . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . '"';
        $html .= ' name="' . $this->getFieldNamePrefix() . $attribute->getAttributeCode() . $suffix . '"';
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
                    if (is_array($attribute->getData('current_value')) &&
                        in_array($option['value'], $attribute->getData('current_value'))) {
                        $selected = ' selected="selected"';
                    } else {
                        if ($attribute->getData('current_value')) {
                            if ($attribute->getData('current_value') == $option['value']) {
                                $selected = ' selected="selected"';
                            }
                        } elseif ($option['value'] == $attribute->getDefaultValue()) {
                            $selected = ' selected="selected"';
                        }
                        /*$selected = ($attribute->getData('current_value') == $option['value'] ||
                    $option['value'] == $attribute->getDefaultValue()) ? ' selected="selected"' : '';*/
                    }

                    $html .= '<option value="' . $option['value'] . '"' . $selected . '>' . $option['label'] . '</option>';
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
        $optionalClasses = '';
        $numericAttributeCodes = ['product_length', 'product_width', 'product_height', 'return_period'];
        if (in_array($attribute->getAttributeCode(), $numericAttributeCodes)) {
            $optionalClasses = 'validate-not-negative-number';
        }
        $requiredClass = $attribute->getIsRequired() ? ' required-entry' : '';
        $html = '<input class="input-text' . $requiredClass . ' ' . $optionalClasses . '"';
        $html .= ' type="text"';
        $html .= ' id="' . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . '"';
        $html .= ' name="' . $this->getFieldNamePrefix()
        . $attribute->getAttributeCode() . $this->getFieldNameSuffix() . '"';

        if ($attribute->getAttributeCode() == 'return_period') {
            $html .= ' maxlength="5"';
        } else {
            $html .= ' maxlength="255"';
        }
        if ($this->isView()) {
            $html .= ' disabled ';
        }
        $html .= ' value="' . htmlspecialchars($attribute->getData('current_value')) . '"';
        if ($attribute->getIsRequired()) {
            $html .= ' data-validate="{required:true}"';
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
        $html = '<input class="input-text validate-not-negative-number ' . $requiredClass . '"';
        $html .= ' type="text"';
        $html .= ' id="' . $this->getHtmlIdPrefix() . $attribute->getAttributeCode() . '"';
        $html .= ' name="' . $this->getFieldNamePrefix() . $attribute->getAttributeCode();
        $html .= $this->getFieldNameSuffix() . '" value="' . $attribute->getData('current_value') . '"';
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
        $html .= ' name="' . $this->getFieldNamePrefix() . $attribute->getAttributeCode()
        . $this->getFieldNameSuffix() . '" ';
        if ($attribute->getIsRequired()) {
            $html .= ' data-validate="{required:true}"';
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
        $html .= ' name="' . $this->getFieldNamePrefix()
        . $attribute->getAttributeCode() . $this->getFieldNameSuffix() . '"';
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
     */
    public function getAttributes()
    {
        $attributeSetId = ($this->getCategoryAttributeSetId()) ?: 4;
        $product = $this->productFactory->create();
        $storeId = ($this->_request->getParam('store')) ? $this->_request->getParam('store') : 0;
        $product->setStoreId($storeId);
        $product->setTypeId('simple');
        $product->setAttributeSetId($attributeSetId);
        /** @var $group \Magento\Eav\Model\Entity\Attribute\Group */
        $attributes = $product->getAttributes();

        $excludeAttributes = $this->getExcludeAttributeList();

        foreach ($attributes as $key => $attribute) {
            if (!$attribute->getIsVisible() ||
                in_array($attribute->getAttributeId(), $excludeAttributes)
            ) {
                unset($attributes[$key]);
            } elseif ($attribute->getFrontendInput() == 'select' &&
                $attribute->getIsUserDefined() &&
                $attribute->getIsGlobal() == 1) {
                $this->variantAttributes[$key] = $attribute;
            } elseif ($attribute->getIsRequired()) {
                if ($storeId == 0) {
                    $this->vitalAttributes[$key] = $attribute;
                } else {
                    if ($attribute->getIsGlobal() == 0) {
                        $this->vitalAttributes[$key] = $attribute;
                    }
                }
            } else {
                /* Remove scope condition because attribute with any scope can load in all store */
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
            if ($attributes) {
                foreach ($attributes as $attribute) {
                    if (!($attribute->getFrontendInput() === null)) {
                        $attribute = $this->setPlaceholderIfExists($attribute);

                        $required = $attribute->getIsRequired() ? ' required' : '';
                        $html .= '<div class="field ' . $this->getHtmlIdPrefix()
                        . $attribute->getAttributeCode() . $required . '">';

                        $html .= '<label class="label" for="' . $this->getHtmlIdPrefix()
                        . $attribute->getAttributeCode() . '">';
                        $html .= '<span>' . __($attribute->getStoreLabel()) . '</span>';
                        $html .= '</label>';

                        $html .= '<div class="control _with-tooltip">';
                        $html .= $this->setFrontendInputToHtml($attribute);
                        $html .= '</div>';

                        $html .= '</div>';
                    }
                }
            }

            return $html;
        } catch (\Exception $exc) {
            throw new \Exception($exc->getMessage());
        }
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBreadCrumb()
    {
        $html = '<div class="breadcrumbs">';
        $html .= '<div class="page-main">';
        $html .= '<ul class="items">';
        $html .= '<li class="item home">' . __('Root Catalog') . '</li>';
        $categoryIds = explode('/', $this->getLoadedCategory()->getPath());

        //removed root category and defaul category
        unset($categoryIds[0]);
        unset($categoryIds[1]);

        foreach ($categoryIds as $_categoryId) {
            $category = $this->categoryRepository->get($_categoryId);
            if ($this->escapeHtml($category->getName()) == $this->escapeHtml($this->getLoadedCategory()->getName())) {
                $html .= '<li class="item"><strong>' . $category->getName() . '</strong></li>';
            } else {
                $html .= '<li class="item">' . $category->getName() . '</li>';
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
        return $this->getUrl('rbcatalog/product/uniquesku', ['id' => $this->getRequest()->getParam('id')]);
    }

    /**
     * @return string
     */
    public function getUniquemanufacturerskuPostActionUrl()
    {
        return $this->getUrl('rbcatalog/product/uniquemanufacturersku');
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

    /**
     * Retrieve tree Categories
     * @return string
     */
    private function _getTreeCategories($parent, $isChild)
    {
        $selectedCategoryIds = $this->getFormData();

        if (!empty($selectedCategoryIds)) {
            $selectedCategoryIds = $selectedCategoryIds['form_data'];
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();

        $vendorCats = $this->_vendorSession->getVendor()->getCategory();

        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', ['eq' => $parent->getId()])
            ->addAttributeToFilter('entity_id', ['neq' => $parent->getId()])
            ->addAttributeToSort('position', 'asc')
            ->load();
        $currentlevel = $parent->getLevel() + 1;
        $html = '<ul class="category-ul level-' . $currentlevel . '">';
        foreach ($collection as $category) {
            $class = 'level-' . $category->getLevel();
            if ($category->getLevel() > $currentlevel) {
                $html .= '<ul class="' . $class . '">';
            } elseif ($category->getLevel() < $currentlevel) {
                $html .= '</ul><ul class="' . $class . '">';
            }

            $childClass = '';
            if ($category->hasChildren()) {
                $childClass = ($category->getLevel() == 2) ? ' base has-children' : ' has-children';
            }

            $html .= '<li class="item level-' . $category->getLevel() . $childClass . '">';
            if (!$category->hasChildren()) {
                $checked = '';
                if (isset($selectedCategoryIds['category']) &&
                    in_array($category->getId(), $selectedCategoryIds['category'])) {
                    $checked = 'checked = "checked"';
                }
                $html .= '<input type="checkbox" name="category[]" id="category-' . $category->getId() . '" value="';
                $html .= $category->getId() . '" title="' . $category->getName() . '" ';
                $html .= 'class="checkbox"' . $checked . '/>';
            }

            $html .= '<label for="category-' . $category->getId() . '"><span>' . $category->getName() . "</span></label>";
            $currentlevel = $category->getLevel();
            if ($category->hasChildren()) {
                $html .= $this->_getTreeCategories($category, true);
            }

            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * @return mixed|string
     */
    public function getCurrentStore()
    {
        return $storeId = ($this->_request->getParam('store')) ? $this->_request->getParam('store') : 'default';
    }
}
