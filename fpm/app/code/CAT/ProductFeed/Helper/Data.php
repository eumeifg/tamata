<?php

namespace CAT\ProductFeed\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getProductFeedUrls()
    {
        $storeMediaUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'cat/feed/fb/';
        $data = [];
        $feedFilename = 'default-fb-feed.csv';
        $data[0]['filename'] = $feedFilename;
        $data[0]['name']     = __('Default Facebook Product Feed');
        $data[0]['url']      = $storeMediaUrl . $feedFilename;
        return $data;
    }
}