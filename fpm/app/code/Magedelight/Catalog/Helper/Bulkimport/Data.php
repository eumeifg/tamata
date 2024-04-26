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
namespace Magedelight\Catalog\Helper\Bulkimport;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Data extends AbstractHelper
{

    const XML_PATH_BULKIMPORT_ENABLED = 'md_bulkimport/general/enable';
    const XML_PATH_ADMINNOTIFICATION_ENABLED = 'md_bulkimport/general/email_enable';
    const XML_PATH_ADMINNOTIFICATION_EMAIL = 'md_bulkimport/general/static_width';

    /**
     *
     * @param string $field
     * @param integer $storeId
     * @return mixed
     */
    public function getConfigValue($field = '', $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param integer $storeId
     * @return boolean
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_BULKIMPORT_ENABLED, $storeId);
    }

    /**
     *
     * @param integer $storeId
     * @return boolean
     */
    public function isNotificationEnabled($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_ADMINNOTIFICATION_ENABLED, $storeId);
    }

    /**
     *
     * @param integer $storeId
     * @return string
     */
    public function getAdminEmail($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_ADMINNOTIFICATION_EMAIL, $storeId);
    }
    
    /**
     *
     * @return array
     */
    public function getCSVHeaders()
    {
        return [
                "category_id"  => "category_id",
                "name" => "name",
                "has_variants" => "has_variants",
                "vendor_sku" => "vendor_sku",
                "super_sku" => "super_sku",
                "condition" => "condition",
                "condition_note" => "condition_note",
                "price" => "price",
                "special_price" => "special_price",
                "special_from_date" => "special_from_date",
                "special_to_date" => "special_to_date",
                "qty" => "qty",
                "reorder_level" => "reorder_level",
                "warranty_type" => "warranty_type",
                "warranty_description" => "warranty_description",
                "images" => "images",
                "base_image" => "base_image"
            ];
    }
    
    /**
     *
     * @param integer $category_id
     * @return NULL|array
     */
    public function getSampleRow($category_id = null)
    {
        if ($category_id) {
            $row = [
                "category_id"  => $category_id,
                "name" => 'Enter Product Name',
                "has_variants" => "0",
                "vendor_sku" => 'Enter Vendor Product SKU',
                "super_sku" => null,
                "condition" => "0",
                "condition_note" => 'Condition Note',
                "price" => '100',
                "special_price" => '80',
                "special_from_date" => '2017-02-01',
                "special_to_date" => '2017-02-20',
                "qty" => '1000',
                "reorder_level" => '10',
                "warranty_type" => '1',
                "warranty_description" => null,
                "images" => 'example.jpg,abc.jpg',
                "base_image" => 'example.jpg'
            ];
            return $row;
        }
        return null;
    }
    
    /**
     *
     * @return array
     */
    public function getVitalFields($productType = '')
    {
        if ($productType === Configurable::TYPE_CODE) {
            return [
                "category_id",
                "name",
                "vendor_sku",
                "has_variants",
                "images",
                "base_image"
            ];
        }
        return [
            "category_id",
            "name",
            "vendor_sku",
            "images",
            "base_image",
            "has_variants",
            "price",
            "condition",
            "qty"
        ];
    }

    /**
     *
     * @return array
     */
    public function getNonZeroFields()
    {
         return [
                
                "price",
                "special_price",
                "qty"
            ];
    }
    
    /**
     *
     * @return array
     */
    public function getSystemExcludedAttributes()
    {
        return ['old_id', 'required_options', 'has_options', 'created_at', 'updated_at', 'url_path', 'links_exist'];
    }
    
    /* custom attributes to eliminate redundant attributes. */
    /**
     *
     * @return array
     */
    public function getCustomExcludedAttributes()
    {
        return ['name'];
    }
    
    /**
     *
     * @return string
     */
    public function getCleanupTime()
    {
        $time = str_replace(',', ':', $this->getConfigValue('md_bulkimport/cleanup/time'));
        $frequency = $this->getConfigValue('md_bulkimport/cleanup/frequency');
        $timezone = $this->getConfigValue('general/locale/timezone');
        switch ($frequency) {
            case 'D':
                $frequency = 'Day';
                break;
            case 'W':
                $frequency = 'Week';
                break;
            case 'M':
                $frequency = 'Month';
                break;
            default:
                $frequency = '';
                break;
        }
        return $time . ' (' . $timezone . ') every ' . $frequency;
    }
    
    /**
     *
     * @return string
     */
    public function getCSVTemplateText($headers = [])
    {
        $text = "";
        $count = 1;
        $totalColumns = count($headers);
        foreach ($headers as $key => $value) {
            $text .= "\"{{".$key."}}\"";
            $text .= ($count < $totalColumns)?",":"";
            $count++;
        }
        return $text;
    }
    
    /**
     *
     * @param array $headerFields
     * @param array $attributes
     * @return array
     */
    public function getExtraAttributeHeaders($headerFields = [], $attributes = [])
    {
        /*set appropriate heading names*/
        if (!empty($headerFields) && !empty($attributes)) {
            foreach ($attributes as $attributes_data => $attnames) {
                $headerFields[$attnames] = $attnames;
            }
        }
        return $headerFields;
    }
}
