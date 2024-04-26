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
namespace Magedelight\Catalog\Plugin\Model;

use Magedelight\Catalog\Model\Config\Source\DefaultVendor\Criteria;
use Magento\Config\Model\Config as CoreConfig;

class Config
{

    /**
     * @var \Magedelight\Catalog\Model\Indexer\VendorProduct
     */
    protected $vendorProductIndexer;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Criteria
     */
    protected $criteria;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magedelight\Catalog\Model\Indexer\VendorProduct $vendorProductIndexer
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Criteria $criteria
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magedelight\Catalog\Model\Indexer\VendorProduct $vendorProductIndexer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Criteria $criteria,
        \Magento\Framework\Registry $registry
    ) {
        $this->vendorProductIndexer = $vendorProductIndexer;
        $this->scopeConfig = $scopeConfig;
        $this->criteria = $criteria;
        $this->registry = $registry;
    }

    public function beforeSave(CoreConfig $subject)
    {
        if ($subject->getSection() == 'vendor_product') {
            $this->registry->register('precedence_1', $this->scopeConfig->getValue(Criteria::PRECEDENCE_1_XML_PATH));
            $this->registry->register('precedence_2', $this->scopeConfig->getValue(Criteria::PRECEDENCE_2_XML_PATH));
            $this->registry->register('precedence_3', $this->scopeConfig->getValue(Criteria::PRECEDENCE_3_XML_PATH));
            $this->registry->register(
                'default_precedence',
                $this->scopeConfig->getValue(Criteria::DEFAULT_PRECEDENCE_XML_PATH)
            );
        }
    }

    public function afterSave(CoreConfig $subject, $result)
    {
        if ($subject->getSection() == 'vendor_product') {
            $isCriteriaChanged = false;
            if ($this->scopeConfig->getValue(Criteria::PRECEDENCE_1_XML_PATH)
                    != $this->registry->registry('precedence_1')
            ) {
                $isCriteriaChanged = true;
            }

            if ($this->scopeConfig->getValue(Criteria::PRECEDENCE_2_XML_PATH)
                    != $this->registry->registry('precedence_2')
            ) {
                $isCriteriaChanged = true;
            }

            if ($this->scopeConfig->getValue(Criteria::PRECEDENCE_3_XML_PATH)
                    != $this->registry->registry('precedence_3')
            ) {
                $isCriteriaChanged = true;
            }

            if (empty($this->criteria->getActiveCriteriasForDefaultVendor(false))
                && $this->scopeConfig->getValue(Criteria::DEFAULT_PRECEDENCE_XML_PATH)
                != $this->registry->registry('default_precedence')
            ) {
                $isCriteriaChanged = true;
            }

            if ($isCriteriaChanged) {
                $this->vendorProductIndexer->executeFull();
            }
        }
    }
}
