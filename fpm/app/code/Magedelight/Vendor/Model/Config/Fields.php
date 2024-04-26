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
namespace Magedelight\Vendor\Model\Config;

class Fields
{
    protected $personalDataFields = [];

    protected $businessDataFields = [];

    protected $bankingDataFields = [];

    protected $shippingDataFields = [];

    protected $removeDataFields = [];

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $personalDataFields
     * @param array $businessDataFields
     * @param array $bankingDataFields
     * @param array $shippingDataFields
     * @param array $removeDataFields
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $personalDataFields = [],
        array $businessDataFields = [],
        array $bankingDataFields = [],
        array $shippingDataFields = [],
        array $removeDataFields = []
    ) {
        $this->personalDataFields = $personalDataFields;
        $this->businessDataFields = $businessDataFields;
        $this->bankingDataFields = $bankingDataFields;
        $this->shippingDataFields = $shippingDataFields;
        $this->removeDataFields = $removeDataFields;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     *
     * @return array
     */
    public function getBusinessFields()
    {
        return array_diff_key($this->businessDataFields, $this->removeDataFields);
    }

    /**
     *
     * @return array
     */
    public function getPersonalFields()
    {
        return array_diff_key($this->personalDataFields, $this->removeDataFields);
    }

    /**
     *
     * @return array
     */
    public function getShippingFields()
    {
        return array_diff_key($this->shippingDataFields, $this->removeDataFields);
    }
}
