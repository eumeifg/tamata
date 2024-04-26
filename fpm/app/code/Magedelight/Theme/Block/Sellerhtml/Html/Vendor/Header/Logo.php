<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Theme
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Theme\Block\Sellerhtml\Html\Vendor\Header;

/**
 * Logo page header block
 */
class Logo extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_COLOR_OPTION_HEAD    = 'vendor_design/vendor_dashboard/vendor_dashboard_color_option_header';
    const XML_PATH_COLOR_OPTION_MAIN    = 'vendor_design/vendor_dashboard/vendor_dashboard_color_option_main';
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'Magedelight_Theme::html/vendor/header/logo.phtml';

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $fileStorageHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper,
        array $data = []
    ) {
        $this->fileStorageHelper = $fileStorageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check if current url is url for home page
     *
     * @return bool
     */
    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }

    /**
     * Get logo image URL
     *
     * @return string
     */
    public function getLogoSrc()
    {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = $this->_getLogoUrl();
        }
        return $this->_data['logo_src'];
    }

    /**
     * Retrieve logo text
     *
     * @return string
     */
    public function getLogoAlt()
    {
        if (empty($this->_data['logo_alt'])) {
            $this->_data['logo_alt'] = $this->_scopeConfig->getValue(
                'design/header/logo_alt',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->_data['logo_alt'];
    }

    /**
     * Retrieve logo width
     *
     * @return int
     */
    public function getLogoWidth()
    {
        if (empty($this->_data['logo_width'])) {
            $this->_data['logo_width'] = $this->_scopeConfig->getValue(
                'design/header/logo_width',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return (int)$this->_data['logo_width'] ?: (int)$this->getLogoImgWidth();
    }

    /**
     * Retrieve logo height
     *
     * @return int
     */
    public function getLogoHeight()
    {
        if (empty($this->_data['logo_height'])) {
            $this->_data['logo_height'] = $this->_scopeConfig->getValue(
                'design/header/logo_height',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return (int)$this->_data['logo_height'] ?: (int)$this->getLogoImgHeight();
    }

    /**
     * Retrieve logo image URL
     *
     * @return string
     */
    protected function _getLogoUrl()
    {
        /*
         * Get frontend theme logo as we don't have a feature to upload vendor panel logo from admin.
         * $folderName = \Magedelight\Vendor\Model\Config\Backend\Image\Logo::UPLOAD_DIR; */
        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;

        $storeLogoPath = $this->_scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        $logoUrl = $this->_urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;

        if ($storeLogoPath !== null && $this->_isFile($path)) {
            $url = $logoUrl;
        } elseif ($this->getLogoFile()) {
            $url = $this->getViewFileUrl($this->getLogoFile());
        } else {
            $url = $this->getViewFileUrl('images/logo.svg');
        }
        return $url;
    }

    /**
     * If DB file storage is on - find there, otherwise - just file_exists
     *
     * @param string $filename relative path
     * @return bool
     */
    protected function _isFile($filename)
    {
        if ($this->fileStorageHelper->checkDbUsage() && !$this->getMediaDirectory()->isFile($filename)) {
            $this->fileStorageHelper->saveFileToFilesystem($filename);
        }
        return $this->getMediaDirectory()->isFile($filename);
    }

    /**
     *
     * @return string
     */
    public function getHeaderLeftHeadColor()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_COLOR_OPTION_HEAD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     *
     * @return string
     */
    public function getHeaderLeftMainColor()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_COLOR_OPTION_MAIN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
