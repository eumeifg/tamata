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
namespace Magedelight\Vendor\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Catalog image helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Image extends AbstractHelper
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Media config node
     */
    const MEDIA_TYPE_CONFIG_NODE = 'images';

    /**
     * Current model
     *
     * @var \Magedelight\Vendor\Model\Vendor\Image
     */
    protected $_model;

    /**
     * Current Vendor
     *
     * @var \Magedelight\Vendor\Model\Vendor
     */
    protected $_vendor;

    /**
     * Image File
     *
     * @var string
     */
    protected $_imageFile;

    /**
     * Image Placeholder
     *
     * @var string
     */
    protected $_placeholder;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * Vendor image factory
     *
     * @var \Magedelight\Vendor\Model\Vendor\ImageFactory
     */
    protected $_vendorImageFactory;

    protected $_vendorHelper;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magedelight\Vendor\Model\Vendor\ImageFactory $vendorImageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magedelight\Vendor\Model\Vendor\ImageFactory $vendorImageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Helper\Data $vendorHelper
    ) {
        $this->_vendorImageFactory = $vendorImageFactory;
        $this->_assetRepo = $assetRepo;
        $this->_vendorHelper =  $vendorHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Reset all previous data
     *
     * @return $this
     */
    protected function _reset()
    {
        $this->_model = null;
        $this->_vendor = null;
        $this->_imageFile = null;

        return $this;
    }

    /**
     * Initialize Helper to work with Image
     *
     * @param \Magedelight\Vendor\Model\Vendor $product
     * @param string $imageId
     * @param array $attributes
     * @return $this
     */
    public function init($vendor, $file)
    {
        $this->_reset();
        $this->setVendor($vendor);
        $this->setImageFile($file);
        $image = $this->_vendorHelper->getConfigValue('vendor_design/profile/default_avatar');
        $this->placeholder($image);

        return $this;
    }

    /**
     * Set placeholder
     *
     * @param string $fileName
     * @return void
     */
    public function placeholder($fileName)
    {
        $this->_placeholder = $fileName;
    }

    /**
     * Get Placeholder
     *
     * @param null|string $placeholder
     * @return string
     */
    public function getPlaceholder($placeholder = null)
    {
        if ($placeholder) {
            $placeholderFullPath = 'vendor/logo/' . $placeholder . '.jpg';
        } else {
            $placeholderFullPath = $this->_placeholder ? 'vendor/logo/' . $this->_placeholder : '';
        }
        return $placeholderFullPath;
    }

    /**
     * Retrieve image URL
     *
     * @return string
     */
    public function getUrl()
    {
        try {
            if (!$this->_getModel()->setDestinationSubdir('vendor/logo')->getUrl($this->getImageFile())) {
                throw new \Exception();
            }
            return $this->_getModel()->setDestinationSubdir('vendor/logo')->getUrl($this->getImageFile());
        } catch (\Exception $e) {
            return $this->getDefaultPlaceholderUrl();
        }
    }

    /**
     * @param null|string $placeholder
     * @return string
     */
    public function getDefaultPlaceholderUrl($placeholder = null)
    {
        try {
            //$url = $this->_assetRepo->getUrl($this->getPlaceholder($placeholder));
            $url = $this->_urlBuilder->getBaseUrl(
                ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]
            ) . $this->getPlaceholder($placeholder);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $url = $this->_urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
        return $url;
    }

    /**
     * Get current Image model
     *
     * @return \Magedelight\Vendor\Model\Vendor\Image
     */
    protected function _getModel()
    {
        if (!$this->_model) {
            $this->_model = $this->_vendorImageFactory->create();
        }
        return $this->_model;
    }

    /**
     * Set current Vendor
     *
     * @param \Magedelight\Vendor\Model\Vendor $product
     * @return $this
     */
    protected function setVendor($product)
    {
        $this->_vendor = $product;
        return $this;
    }

    /**
     * Get current Vendor
     *
     * @return \Magedelight\Vendor\Model\Vendor
     */
    protected function getVendor()
    {
        return $this->_vendor;
    }

    /**
     * Set Image file
     *
     * @param string $file
     * @return $this
     */
    public function setImageFile($file = 'thumbnail.jpg')
    {
        if (($file === null)) {
            $this->_imageFile = 'thumbnail.jpg';
        } else {
            $this->_imageFile = $file;
        }

        return $this;
    }

    /**
     * Get Image file
     *
     * @return string
     */
    protected function getImageFile()
    {
        return $this->_imageFile;
    }
}
