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
namespace Magedelight\Vendor\Model;

use Magento\Store\Model\ScopeInterface;

/**
 * Vendor custom theme design
 * @author Rocket Bazaar Core Team
 *  Created at 24 Feb, 2016 5:17:07 PM
 */
class Design extends \Magento\Framework\Model\AbstractModel
{
    /* taking static theme path for vendor*/
    const VENDOR_THEME_PATH = 'Rocketbazaar/vendor';

    const XML_PATH_THEME_ID_VENDOR = 'design/vendor_theme/theme_id';

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    private $themeCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Design package instance
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design = null;
    protected $_isEmulating = false;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_design = $design;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Apply custom design
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function applyVendorDesign()
    {
        $this->_design->setDesignTheme($this->getVendorThemeId());
        return $this;
    }

    /**
     * Get vendor theme id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVendorThemeId()
    {
        $themeId = $this->scopeConfig->getValue(
            self::XML_PATH_THEME_ID_VENDOR,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getCode()
        );

        if (!$themeId) {
            $themeCol = $this->themeCollectionFactory->create();
            /* Set default theme if not specified in admin design configuration. */
            $themeIds = $themeCol->addFieldToSelect('theme_path')
                ->addFieldToFilter('theme_path', self::VENDOR_THEME_PATH)
                ->getAllIds();
            $themeId = $themeIds[0];
        }
        return $themeId;
    }

    /**
     * @param null $store
     * @param null $area
     * @param null $theme
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setDesignStore($store = null, $area = null, $theme = null)
    {
        /** @var \Magento\Store\Model\App\Emulation $appEmulation */
        $appEmulation = $this->getObjManager(\Magento\Store\Model\App\Emulation::class);
        if (!($store === null)) {
            if ($this->_isEmulating) {
                return $this;
            }
            $this->_isEmulating = true;
            $store = $this->storeManager->getStore($store);
            $appEmulation->startEnvironmentEmulation($store->getId(), $area, true);
            if ($theme) {
                /** @var \Magento\Framework\View\DesignInterface $viewDesign */
                $this->_design->setDesignTheme($theme, $area);
            }
        } else {
            if (!$this->_isEmulating) {
                return $this;
            }
            $appEmulation->stopEnvironmentEmulation();
            $this->_isEmulating = false;
        }

        return $this;
    }

    /**
     * @param $class
     * @return mixed
     */
    public function getObjManager($class)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get($class);
    }
}
