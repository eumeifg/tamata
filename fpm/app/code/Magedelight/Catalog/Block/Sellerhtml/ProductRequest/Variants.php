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

class Variants extends \Magedelight\Catalog\Block\Sellerhtml\ProductRequest\Edit
{
    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributesHtml()
    {
        $this->setHtmlIdPrefix('variant-');
        $this->setFieldNamePrefix('variant[');
        $this->setFieldNameSuffix(']');

        if ($this->isProductHasVariants() || $this->getRequest()->getParam('cid', false)) {
            return $this->getHtml($this->getVariantAttributes());
        }
        return true;
    }

    /**
     *
     * @param $attributes
     * @return boolean|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHtml($attributes)
    {
        if (!($attributes === null)) {
            try {
                $attributeCodes = [];
                $attributeLabels = [];
                $html = '';
                foreach ($attributes as $attribute) {
                    $attributeCodes[] = $attribute->getAttributeCode();
                    $attributeLabels[] = $attribute->getStoreLabel();
                    $attributeIds[$attribute->getAttributeId()] = $attribute->getAttributeCode();
                    $required = $attribute->getIsRequired() ? ' required' : '';
                    $html .= '<div class="field ' . $this->getHtmlIdPrefix() .
                        $attribute->getAttributeCode() . $required . ' variant-container pn ">';
                    $html .= '<div class="control variant-control">';
                    $html .= $this->renderOptionCheckbox($attribute);
                    $html .= '</div>';
                    $html .= '</div>';
                }
                $html .= "<input id=\"variantsAttributeCodes\" type=\"hidden\" name=\"variantsAttributeCodes\" ";
                $html .= "value='" . implode(',', $attributeCodes) . "' class=\"variantsAttributeCodes\">";
                $html .= "<input id=\"variantsAttributeLabels\" type=\"hidden\" name=\"variantsAttributeLabels\" ";
                $html .= "value='" . implode(',', $attributeLabels) . "' class=\"variantsAttributeLabels\">";
                $html .= "<input id=\"usedAttributeIds\" type=\"hidden\" name=\"usedAttributeIds\" ";
                $html .= "value='" . json_encode($attributeIds) . "' class=\"usedAttributeIds\">";
                return $html;
            } catch (Exception $exc) {
                return $exc->getMessage();
            }
        }
        return false;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributesMultiSelectHtml()
    {
        $attributes = $this->getVariantAttributes();
        $html = '';
        if (!is_null($attributes)) {
            try {
                $attributeCodes = [];
                $attributeLabels = [];
                $selectedAttributes = [];
                if ($this->isProductHasVariants()) {
                    $selectedAttributes = $this->getCurrentRequest()->getUsedProductAttributeIds();
                    $selectedAttributes = $this->_jsonDecoder->decode($selectedAttributes);
                }
                $html = '<div class="variant-attribures-content fieldset">';
                foreach ($attributes as $attribute) {
                    $checked = '';
                    $attributeCodes[] = $attribute->getAttributeCode();
                    if (array_key_exists($attribute->getAttributeId(), $selectedAttributes)) {
                        $checked = 'checked';
                    }
                    $attributeLabels[] = $attribute->getStoreLabel();
                    $attributeIds[$attribute->getAttributeId()] = $attribute->getAttributeCode();
                    $html .= '<div class="field ' . $attribute->getAttributeCode() . '">';
                    $html .= '<div class="control variant-attributes-control">';
                    $html .= '<input type="checkbox" ' . $checked . ' name="' . $attribute->getStoreLabel() . '" id="' . $attribute->getAttributeCode() . '" value="' . $attribute->getAttributeId() . '" class="checkbox options-selector">
                            <label for="' . $attribute->getStoreLabel() . '">' . $attribute->getStoreLabel() . '</label>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
                $html .= '<button type="button" title="' . __('Get Options') . '" id="get_options_btn" class="action button secondary"><span>' . __('Get Options') . '</span></button>';
                $html .= '</div>';
            } catch (Exception $exc) {
                return $exc->getMessage();
            }
        }
        return $html;
    }

    /**
     * @return \Magedelight\Catalog\Model\Source\available
     */
    public function getProductConditionOption()
    {
        return $this->_productCondition->toOptionArray();
    }

    /**
     * @return \Magedelight\Catalog\Model\Source\available
     */
    public function getWarrantyTypeOptions()
    {
        return $this->warrantyBy->toOptionArray();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductConditionOptionString()
    {
        $isNew = $this->getProduct()->getData('condition');
        if ($isNew == '' || $isNew == null) {
            $isNew = 1;
        }
        $html = '';
        foreach ($this->getProductConditionOption() as $_option) {
            $html .= "<option value='" . $_option['value'] . "'>" . $_option['label'] . "</option>";
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getConditionNoteAttribute()
    {
        $html  = '';
        $html .= "<div class='field item-condition-note'>
            <label class='label' for='item-condition-note'>
                <span>Condition Note</span>
            </label>
            <div class='control _with-tooltip'>
                <textarea class='input-text'
                          id='item-condition-note'
                          name='offer[condition_note]'
                          placeholder='Condition Note'></textarea>
                          <div class='field-tooltip toggle'>
                                   <span class='field-tooltip-action action-help' tabindex='0' hidden='hidden'></span>
                                    <div class='field-tooltip-content'>
                                         <span>Enter Condition Note</span>
                                    </div>
                                </div>
            </div>
        </div>";
        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function renderConfigFields()
    {
        $html = '';
        $html .= '<div class="field item-condition required">
            <label class="label">
                <span>Condition</span>
            </label>
            <div class="control select _with-tooltip">
            <select name="offer[condition]" id="condition" data-validate="{required:true}">' .
            $this->getProductConditionOptionString() . '
            </select>
            <div class="field-tooltip toggle">
              <span class="field-tooltip-action action-help" tabindex="0" hidden="hidden"></span>
                <div class="field-tooltip-content">
                 <span>Select Condition</span>
            </div>
            </div>
            </div>
        </div>';
        return $html;
    }

    /**
     * @param $attribute
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function renderOptionCheckbox($attribute)
    {
        $disabled ='';
        if ($this->isView()):
            $disabled = 'disabled';
        endif;
        $html = '';
        $html .= '<ul class="variant-group">
                 <label for="" class="parent-label"><span>' . __($attribute->getStoreLabel()) . '</span></label> ';
        if ($attribute->getOptions()) {
            foreach ($attribute->getOptions() as $option) {
                $checked = '';
                if ($option['value']) {
                    if ($this->isRequestResubmitted()) {
                        if ($this->keepCheckedIfUsed($attribute->getAttributeCode(), $option['value'])) {
                            $checked = 'checked';
                        }
                    }
                    $html .= '<li class="item">                        
                            <input type="checkbox" ' . $checked . ' name="' . $attribute->getAttributeCode() . '[]" id="' . $attribute->getAttributeCode() . $option['value'] . '" value="' . $option['value'] . '" class="checkbox"' . $disabled . '>
                            <label for="' . $attribute->getAttributeCode() . $option['value'] . '">' . $option['label'] . '</label>                        
                    </li>';
                }
            }
        }
        $html .='  </ul>';
        return $html;
    }

    /**
     * @return string
     */
    public function getYesNoDropdown()
    {
        $selected = $this->isProductEditMode() ? 'selected="selected"' :
            (($this->getRequest()->getParam('id', false) != false) &&
                ($this->isProductHasVariants())) ? 'selected="selected"' : '';
        $disabledNo = '';
        $disabledYes = ($this->isRequestResubmitted() && !$this->isProductHasVariants()) ? 'disabled' : '';
        $html = '<select name="has_variants" id="has-variants" data-validate="{required:true}"';
        if ($this->isView()) {
            $html .= ' disabled ';
        }
        $html .= '>';
        if (!$this->isProductHasVariants()) {
            $html .='<option value="0" ' . $disabledNo . '>' . __('No') . '</option>';
        }
        $html .='<option value="1" ' . $selected . $disabledYes . '>' . __('Yes') . '</option>';
        $html .= '</select>';
        return $html;
    }

    /**
     * @return $this|array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariantsData()
    {
        $model = $this->getCurrentRequest();
        $collection = $this->_productRequestCollectionFactory->create();
        $collection->getSelect()->join(
            ['mvprw' => 'md_vendor_product_request_website'],
            'mvprw.product_request_id = main_table.product_request_id AND mvprw.website_id = '
            . $this->_storeManager->getStore()->getWebsiteId(),
            ['*']
        );
        $collection->getSelect()->join(
            ['mvprs' => 'md_vendor_product_request_store'],
            'mvprs.product_request_id = main_table.product_request_id AND mvprs.store_id = '
            . $this->_storeManager->getStore()->getId(),
            ['*']
        );
        $collection->getSelect()->join(
            ['mvprsl' => 'md_vendor_product_request_super_link'],
            'mvprsl.product_request_id = main_table.product_request_id AND mvprsl.parent_id = '
            . $model->getProductRequestId(),
            ['parent_id']
        );
        if ($collection && $collection->getSize() > 0) {
            $finalData = [];
            foreach ($collection as $variant) {
                $options = $this->_jsonDecoder->decode($variant->getAttributes());
                $variantData = $variant->getData();
                foreach ($options as $index => $value) {
                    $variantData[$index] = $options[$index];
                }
                $finalData[] = $variantData;
            }
            return $finalData;
        }
        return $this;
    }

    /**
     * @param bool $isNew
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariantColumns($isNew = false)
    {
        $storeId = 0;
        if ($isNew) {
            return $this->helper->getVariantColumns($storeId);
        } else {
            $columns = $this->helper->getVariantColumns($storeId);
            $attributeCodes = $this->getUsedProductAttributeIds();
            $res = array_slice($columns, 0, 1, true) + $attributeCodes
                + array_slice($columns, 1, count($columns) - 1, true);
            return $res;
        }
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUsedProductAttributeIds()
    {
        $usedAttributeIds = $this->getCurrentRequest()->getUsedProductAttributeIds();
        if ($usedAttributeIds) {
            return $this->_jsonDecoder->decode($usedAttributeIds);
        }
    }

    /**
     * replace space with underscore in string
     * @param type $string
     * @return string
     */
    public function getFieldName($string = null)
    {

        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "_", $string);

        return $string;
    }

    /**
     * @param null $string
     * @return string|string[]|null
     */
    public function getFieldNameWithDashSeperator($string = null)
    {

        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);

        return $string;
    }

    /**
     * @param $field
     * @param $value
     * @param int $i
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function renderField($field, $value, $i = 0)
    {
        $disabled = '';
        if ($this->isView()):
            $disabled = 'disabled';
        endif;
        $attributeCodes = $this->getUsedProductAttributeIds();
        $fieldId = str_replace("_", "-", $i . '_' . $field);
        if (is_array($value)) {
            $value= $value [0];
        }
        if (false !== array_search($field, $attributeCodes)) {
            $attribute = $this->_eavConfig->getAttribute('catalog_product', $field);
            $value = "<label>" . $attribute->getSource()->getOptionText($value) . "</label>"
                . "<input type='hidden' id='" . $fieldId . "' name='variants_data[" . $i . "][" . $field . "]' value='" . $attribute->getSource()->getOptionId($value) . "' . $disabled />";
        } elseif ($field == 'condition') {
            $selectedValue =  $value;
            $value = '<select id="' . $fieldId . '" name="variants_data[' . $i . '][' . $field . ']"' . $disabled . ' >';
            foreach ($this->getProductConditionOption() as $_option) {
                $selected = ($_option['value'] == $selectedValue) ? 'selected' : '';
                $value .= "<option value='" . $_option['value'] . "' " . $selected . ">" . $_option['label'] . "</option>";
            }
            $value .= '</select>';
        } elseif ($field == 'warranty_type') {
            $use = '';
            $new = '';
            $selected = ($value == 1) ? $new = 'selected="selected"' : $use = 'selected="selected"';
            $value = '<select id="' . $fieldId . '" name="variants_data[' . $i . '][' . $field . ']" ' . $disabled . ' >'
                . '<option value="1" ' . $new . '>Manufacturer</option>'
                . '<option value="2" ' . $use . '>Vendor</option>'
                . '</select>';
        } elseif ($field == 'action') {
            $value = '<button type="button" title="' . __('Remove') . '" class="action button secondary remove-row btn"><span>' . __('Remove') . '</span></button>';
        } elseif ($field == 'special_from_date') {
            $value = '<div class="control date-field-container"><input class="date-from" type="text" id="date-' . $fieldId . '" name="variants_data[' . $i . '][' . $field . ']" value="' . $value . '"></div>';
        } elseif ($field == 'special_to_date') {
            $value = '<div class="control date-field-container"><input class="date-to" type="text" id="date-' . $fieldId . '" name="variants_data[' . $i . '][' . $field . ']" value="' . $value . '"></div>';
        } elseif (in_array($field, ["vendor_sku", "price", "qty"])) {
            $class = '';
            if (in_array($field, ["price"])) {
                $class = 'price-update';
            }
            if (in_array($field, ["vendor_sku"])) {
                $class = 'vendor_sku vendor-sku';
            }
            if (in_array($field, ["vendor_sku"])) {
                $value .= '<input type="hidden" class="input-text ' . $class . '" id="' . $i . '_' . $field . '" name="variants_data[' . $i . '][' . $field . ']" value="' . $value . '"' . $disabled . '>';
            } else {
                $value = '<input type="text" class="input-text required-entry ' . $class . '" id="' . $i . '_' . $field . '" name="variants_data[' . $i . '][' . $field . ']" value="' . $value . '"' . $disabled . '>';
            }
        } elseif ($field === 'image') {
            $tempValue = $value;
            if ($this->helper->getTmpFileIfExists($tempValue)) {
                $value = '<img class="child_image" data-role="image-element" src="' . $this->mediaConfig->getTmpMediaUrl($tempValue) . '" alt="' . $tempValue . '" />';
                $value .= '<input type="hidden" class="image" id="' . $fieldId . '" name="variants_data[' . $i . '][' . $field . ']" value="' . $tempValue . '" ' . $disabled . '>';
            } else {
                $value .= '<input type="text" class="image" id="' . $fieldId . '" name="variants_data[' . $i . '][' . $field . ']" value="' . $tempValue . '" ' . $disabled . '>';
            }
        } else {
            $class = '';
            if (in_array($field, ["price", "special_price"])) {
                $class = 'price-update';
            }
            $value = '<input type="text" class="' . $class . '" id="' . $fieldId . '" name="variants_data[' . $i . '][' . $field . ']" value="' . $value . '" ' . $disabled . '>';
        }

        return $value;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getDropdownAttributesHtml()
    {
        $this->setHtmlIdPrefix('variant-');
        $this->setFieldNamePrefix('variant[');
        $this->setFieldNameSuffix(']');
        return $this->getDropdownHtml($this->getVariantAttributes());
    }

    /**
     * @param $attributes
     * @return string
     * @throws \Exception
     */
    public function getDropdownHtml($attributes)
    {
        $suffix = 'vendor-';
        $html = '';
        try {
            $attributeCodes = [];
            $attributeLabels = [];
            foreach ($attributes as $attribute) {
                if (!($attribute->getFrontendInput() === null)) {
                    $attributeCodes[] = $attribute->getAttributeCode();
                    $attributeLabels[] = $attribute->getStoreLabel();
                    $attributeIds[$attribute->getAttributeId()] = $attribute->getAttributeCode();

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
            if (!$this->isProductHasVariants()) {
                $html .= "<input id=\"variantsAttributeCodes\" type=\"hidden\" name=\"variantsAttributeCodes\" ";
                $html .= "value='" . implode(',', $attributeCodes) . "' class=\"variantsAttributeCodes\">";
                $html .= "<input id=\"variantsAttributeLabels\" type=\"hidden\" name=\"variantsAttributeLabels\" ";
                $html .= "value='" . implode(',', $attributeLabels) . "' class=\"variantsAttributeLabels\">";
                $html .= "<input id=\"usedAttributeIds\" type=\"hidden\" name=\"usedAttributeIds\" ";
                $html .= "value='" . json_encode($attributeIds) . "' class=\"usedAttributeIds\">";
            }

            return $html;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUsedAttributeCodeValue()
    {
        $storeId = (!$this->getRequest()->getParam("store")) ? 'default' : $this->getRequest()->getParam("store");
        $br = [];
        if ($this->isRequestResubmitted()) {
            $ar = [];
            $variantsData = $this->getVariantsData();
            if ($variantsData) {
                foreach ($this->getUsedProductAttributeIds() as $code) {
                    foreach ($variantsData as $item) {
                        $ar[$code][] = $item[$code];
                    }
                }
                //remove duplication value from array for each attribute code
                foreach ($this->getUsedProductAttributeIds() as $code) {
                    $br[$code] = $ar[$code];
                }
                return $br;
            }
        }
        return $br;
    }

    /**
     * @param $attributeCode
     * @param $optionValue
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function keepCheckedIfUsed($attributeCode, $optionValue)
    {
        $attributeOptionValues = $this->getUsedAttributeCodeValue();
        if (array_key_exists($attributeCode, $attributeOptionValues) &&
            in_array($optionValue, $attributeOptionValues[$attributeCode])) {
            return true;
        }
        return false;
    }
}
