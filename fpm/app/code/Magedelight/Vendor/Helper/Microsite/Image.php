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

class Image extends \Magedelight\Catalog\Helper\Image
{
    protected $_folder = 'vendor/logo';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFileFactory,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        parent::__construct($context, $imageFactory, $filesystem, $storeManager, $imageFileFactory, $file);
    }

    public function getFolderPath()
    {
        return $this->_folder;
    }
}
