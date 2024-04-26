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
namespace Magedelight\Vendor\Helper\Microsite;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\UrlInterface;

class Data extends AbstractHelper
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magedelight\Vendor\Model\Microsite
     */
    protected $_micrositeModel;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magedelight\Vendor\Model\Microsite $micrositeModel
    ) {
        $this->storeManager = $storeManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->filterManager = $filterManager;
        $this->_micrositeModel = $micrositeModel;
        parent::__construct($context);
    }

    /**
     * @param string $url
     * @param string $store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlParams($url, $store = null)
    {
        if ($store === null) {
            $store = $this->storeManager->getStore();
        }
        if ($this->storeManager->getDefaultStoreView() &&
            $store->getId() != $this->storeManager->getDefaultStoreView()->getId()) {
            $url .= '&___store=' . $store->getCode();
        }

        $urlParams = new \Magento\Framework\DataObject([
            'helper' => $this,
            'params' => [],
        ]);

        $params = $urlParams->getParams();
        if (count($params)) {
            $url .= '&' . http_build_query($urlParams->getParams(), '', '&');
        }
        return $url;
    }

    /**
     * @param string $banner
     * @param string $subPath
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMicrositeFileUrl($banner = '', $subPath = 'microsite')
    {
        return $this->storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        ) . $subPath . $banner;
    }

    /**
     * Generate url key based on url_key entered by merchant or page title
     *
     * @param string $identifier
     * @return string
     * @api
     */
    public function generateUrlKey($identifier)
    {
        $urlKey = $identifier;
        return $this->filterManager->translitUrl($urlKey === '' || $urlKey === null ? '' : $urlKey);
    }

    /**
     *
     * @param integer $vendorId
     * @return string
     */
    public function getVendorMicrositeUrl($vendorId = null)
    {
        $vendorId = (int)$vendorId;
        if ($vendorId) {
            $micrositeUrl = $this->_micrositeModel->getVendorMicrositeUrl($vendorId);
            if ($micrositeUrl) {
                return $this->_urlBuilder->getUrl($micrositeUrl);
            }
            // else {
            //     return $this->_urlBuilder->getUrl('rbvendor/microsite_vendor/product/vid/' . $vendorId);
            // }
        }
    }
}
