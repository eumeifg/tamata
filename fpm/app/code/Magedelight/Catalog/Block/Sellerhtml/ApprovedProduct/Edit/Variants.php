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
namespace Magedelight\Catalog\Block\Sellerhtml\ApprovedProduct\Edit;

class Variants extends \Magedelight\Catalog\Block\Sellerhtml\ApprovedProduct\Edit
{
    /**
     * @return bool|string
     * @throws \Exception
     */
    public function getAttributesHtml()
    {
        $this->setHtmlIdPrefix('variant-');
        $this->setFieldNamePrefix('variant[');
        $this->setFieldNameSuffix(']');
        return $this->getHtml($this->getVariantAttributes());
    }

    /**
     * Retrieve list of attributes with admin store label containing $labelPart
     *
     * @param $attributes
     * @return bool|string
     * @throws \Exception
     */
    /*
      public function getSuggestedAttributes()
      {

      $attributeList = $this->attributeList->getSuggestedAttributes($this->getRequest()->getParam('label_part'));
      $excludeAttributes = $this->getExcludeAttributeList();

      foreach ($attributeList as $key => $attribute) {
      if (in_array($key, $excludeAttributes)) {
      unset($attributeList[$key]);
      }
      }
      return $this->getHtml($attributeList);
      }
     */

    /**
     * @param $attributes
     * @return bool|string
     * @throws \Exception
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
                $html .= "value='" . json_encode($attributeIds) . "' class=\"variantsAttributeCodes\">";
                return $html;
            } catch (Exception $exc) {
                throw new \Exception($exc->getMessage());
            }
        }
        return false;
    }

    /**
     * @param $attribute
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function renderOptionCheckbox($attribute)
    {
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
                            <input type="checkbox" ' . $checked . ' name="' . $attribute->getAttributeCode() . '[]" id="' . $attribute->getAttributeCode() . $option['value'] . '" value="' . $option['value'] . '" class="checkbox">
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getYesNoDropdown()
    {
        $html = '<select name="has_variants" id="has-variants" data-validate="{required:true}"';
        if ($this->isView()) {
            $html .= ' disabled ';
        }
        $html .= '>';
        if ($this->isProductEditMode()) {
            if ($this->hasVariants()) {
                $html .='<option value="1" >' . __('Yes') . '</option>';
            } else {
                $html .='<option value="0" >' . __('No') . '</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }

    /**
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariantsData()
    {
        $model = $this->getProduct();
        $ids = [];
        if ($model->getTypeInstance()->getUsedProducts($this->getProduct())) {
            foreach ($model->getTypeInstance()->getUsedProducts($this->getProduct()) as $child) {
                $collection = $this->_vendorProductFactory->create()->getCollection();
                $collection->addFieldToFilter('marketplace_product_id', ['eq' => $child->getId()]);
                $collection->addFieldToFilter('rv.vendor_id', ['eq' => $this->getVendor()->getVendorId()]);

                if ($collection->getFirstItem()->getVendorProductId()) {
                    $ids[] = $collection->getFirstItem();
                }
            }
            return $ids;
        }
        return null;
    }

    /**
     *
     * @param bool $isNew
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariantColumns($isNew = false)
    {
        $storeId = ($this->_request->getParam('store')) ? $this->_request->getParam('store') : 0;
        if ($isNew) {
            return $this->helper->getVariantColumns($storeId);
        } else {
            $columns = $this->helper->getVariantColumns($storeId);
            $attributeCodes = $this->getUsedProductAttributeIds();
            $res = array_slice($columns, 0, 1, true) + $attributeCodes +
                array_slice($columns, 1, count($columns) - 1, true);
            return $res;
        }
    }

    /**
     * Get used product attributes
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getUsedAttributes()
    {
        return $this->_configurableType->getUsedProductAttributes($this->getProduct());
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUsedProductAttributeIds()
    {
        $attributes = (array) $this->_configurableType->getConfigurableAttributesAsArray($this->getProduct());
        $ar = [];
        foreach ($attributes as $key => $attribute) {
            $ar[$key] = $attribute['attribute_code'];
        }
        return $ar;
    }

    /**
     * Retrieve actual list of associated products, array key is obtained from varying attributes values
     *
     * @return Product[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAssociatedProducts()
    {
        $productByUsedAttributes = [];
        foreach ($this->_getAssociatedProducts() as $product) {
            $keys = [];
            foreach ($this->getUsedAttributes() as $attribute) {
                /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                $keys[] = $product->getData($attribute->getAttributeCode());
            }
            $productByUsedAttributes[implode('-', $keys)] = $product;
        }
        return $productByUsedAttributes;
    }

    /**
     * Retrieve actual list of associated products (i.e. if product contains variations matrix form data
     * - previously saved in database relations are not considered)
     *
     * @return Product[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getAssociatedProducts()
    {
        $product = $this->getProduct();
        $ids = $this->getProduct()->getAssociatedProductIds();
        if ($ids === null) {
            // form data overrides any relations stored in database
            return $this->_configurableType->getUsedProducts($product);
        }
        $products = [];
        foreach ($ids as $productId) {
            try {
                $products[] = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }
        return $products;
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
     *
     * @param type $field
     * @param type $value
     * @param int $i
     * @param bool $_item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function renderField($field, $value, $i = 0, $_item = false)
    {
        $attributeCodes = $this->getUsedProductAttributeIds();
        $fieldId = str_replace("_", "-", $i . '_' . $field);
        $html = '';
        /*
          if($field == 'price' || $field == 'special_price')
          {
          $value  = $this->_priceHelper->currency($value);
          } */
        if (false !== array_search($field, $attributeCodes)) {
            $attribute = $this->_eavConfig->getAttribute('catalog_product', $field);
            $vpId = $_item['vendor_product_id'];
            $optionValue = $this->productRepository->getById($value)->getData($field);
            $html = "<label>" . $attribute->getSource()->getOptionText($optionValue) . "</label>" . "<input type='hidden' id='" . $fieldId . "' name='variants_data[" . $i . "][" . $field . "]' value='" . $attribute->getSource()->getOptionId($optionValue) . "' />" . "<input type='hidden' name='variants_data[" . $i . "][" . 'vendor_product_id' . "]' value='" . $vpId . "' />";
        } elseif ($field == 'condition') {
            $html = '<select id="' . $fieldId . '" name="variants_data[' . $i . '][' . $field . ']" >';
            foreach ($this->_productCondition->toOptionArray() as $options) {
                $selected = ($_item['condition'] == $options['value']) ? 'selected="selected"' : '';
                $html .= '<option ' . $selected . ' value="' . $options['value'] . '">';
                $html .= $options['label'] . '</option>';
            }
            $html .= '</select>';
        } elseif ($field == 'warranty_type') {
            $use = '';
            $new = '';
            $selected = ($value == 1) ? $new = 'selected="selected"' : $use = 'selected="selected"';
            $html = '<select id="' . $fieldId . '" name="variants_data[' . $i . '][' . $field . ']" >'
                . '<option value="1" ' . $new . '>' . __('Manufacturer') . '</option>'
                . '<option value="2" ' . $use . '>' . __('Vendor') . '</option>'
                . '</select>';
        } elseif ($field == 'action') {
            $html = '<button type="button" title="' . __('Remove') . '" class="action button secondary remove-row btn">';
            $html .= '<span>' . __('Remove') . '</span></button>';
        } elseif ($field == 'special_from_date') {
            $html = '<div class="date-field-container"><input class="date-from" type="text" id="date-' . $fieldId . '" ';
            $html .= 'name="variants_data[' . $i . '][' . $field . ']" value="' . $value . '"></div>';
        } elseif ($field == 'special_to_date') {
            $html = '<div class="date-field-container"><input class="date-to" type="text" id="date-' . $fieldId . '" ';
            $html .= 'name="variants_data[' . $i . '][' . $field . ']" value="' . $value . '"></div>';
        } elseif (in_array($field, ["vendor_sku"])) {
            $html = '<input type="text" class="input-text required-entry vendor-sku" id="' . $i . '_' . $field . '" ';
            $html .= 'name="variants_data[' . $i . '][' . $field . ']" value="' . $value . '">';
        } elseif (in_array($field, ["price", "qty"])) {
            $html = '<input type="text" class="input-text required-entry" id="' . $i . '_' . $field . '" ';
            $html .= 'name="variants_data[' . $i . '][' . $field . ']" value="' . $value . '">';
        } else {
            $html = '<input type="text" id="' . $fieldId . '" name="variants_data[' . $i . '][' . $field . ']" ';
            $html .= 'value="' . $value . '" >';
        }
        return $html;
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
        $attributeCodes = [];
        $attributeLabels = [];
        $attributeIds = [];
        try {
            if ($attributes) {
                foreach ($attributes as $attribute) {
                    if (!($attribute->getFrontendInput() === null)) {
                        $attributeCodes[] = $attribute->getAttributeCode();
                        $attributeLabels[] = $attribute->getStoreLabel();
                        $attributeIds[$attribute->getAttributeId()] = $attribute->getAttributeCode();
                        $required = $attribute->getIsRequired() ? ' required' : '';
                        $html .= '<div class="field ' . $this->getHtmlIdPrefix() .
                            $attribute->getAttributeCode() . $required . '">';

                        $html .= '<label class="label" for="' . $this->getHtmlIdPrefix() .
                            $attribute->getAttributeCode() . '">';
                        $html .= '<span>' . __($attribute->getStoreLabel()) . '</span>';
                        $html .= '</label>';

                        $html .= '<div class="control _with-tooltip">';
                        $html .= $this->setFrontendInputToHtml($attribute);
                        $html .= '</div>';

                        $html .= '</div>';
                    }
                }
            }
            if (!$this->isProductHasVariants()) {
                $html .= "<input id=\"variantsAttributeCodes\" type=\"hidden\" name=\"variantsAttributeCodes\" ";
                $html .= "value='" . implode(',', $attributeCodes) . "' class=\"variantsAttributeCodes\">";
                $html .= "<input id=\"variantsAttributeLabels\" type=\"hidden\" name=\"variantsAttributeLabels\" ";
                $html .= "value='" . implode(',', $attributeLabels) . "' class=\"variantsAttributeLabels\">";
                $html .= "<input id=\"usedAttributeIds\" type=\"hidden\" name=\"usedAttributeIds\" ";
                $html .= "value='" . json_encode($attributeIds) . "' class=\"variantsAttributeCodes\">";
            }

            return $html;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @return \Magedelight\Catalog\Model\ProductRequest|mixed|null
     */
    public function getCurrentRequest()
    {
        if (!$this->vendorProduct) {
            $this->vendorProduct = $this->coreRegistry->registry('vendor_current_product_core');
            if ($this->vendorProduct) {
                $attributes = $this->getVariantAttributes();
                if ($attributes) {
                    foreach ($attributes as $attribute) {
                        $this->vendorProduct->setData(
                            $attribute->getAttributeCode(),
                            $this->vendorProduct->getData($attribute->getAttributeCode())
                        );
                    }
                }
            } else {
                return null;
            }
        }
        return $this->vendorProduct;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUsedAttributeCodeValue()
    {
        $br = [];
        if ($this->isRequestResubmitted()) {
            $ar = [];
            $attributes = (array) $this->_configurableType->getConfigurableAttributesAsArray($this->getProduct());
            foreach ($attributes as $key => $attribute) {
                foreach ($attribute['values'] as $value) {
                    $ar[$attribute['attribute_code']][] = $value['value_index'];
                }
            }
        }
        return $ar;
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
        if (!empty($attributeOptionValues) && array_key_exists($attributeCode, $attributeOptionValues)) {
            if (in_array($optionValue, $attributeOptionValues[$attributeCode])) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function hasVariants()
    {
        switch ($this->getProduct()->getTypeId()) {
            case 'simple':
                return false;
            case 'configurable':
                return true;
            default:
                return false;
        }
    }

    /**
     * @param $productId
     * @return float|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildProductWeight($productId)
    {
        return $this->productRepository->getById($productId)->getWeight();
    }
}
