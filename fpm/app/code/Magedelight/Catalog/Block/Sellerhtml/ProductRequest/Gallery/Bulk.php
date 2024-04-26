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
namespace Magedelight\Catalog\Block\Sellerhtml\ProductRequest\Gallery;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magedelight\Backend\Block\Template\Context;

/**
 * Description of Bulk
 *
 * @author Rocket Bazaar Core Team
 */
class Bulk extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
   
    /** @var Image */
    protected $image;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Config
     */
    private $catalogProductMediaConfig;

    /**
     * @param Context $context
     * @param Image $image
     * @param Config $catalogProductMediaConfig
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        Image $image,
        Config $catalogProductMediaConfig,
        ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->image = $image;
        $this->productFactory = $productFactory;
        $this->catalogProductMediaConfig = $catalogProductMediaConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Bulk Images &amp; Price');
    }

    /**
     * @return string
     */
    public function getNoImageUrl()
    {
        return $this->image->getDefaultPlaceholderUrl('thumbnail');
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        $imageTypes = [];
        foreach ($this->catalogProductMediaConfig->getMediaAttributeCodes() as $attributeCode) {
            /* @var $attribute Attribute */
            $imageTypes[$attributeCode] = [
                'code' => $attributeCode,
                'value' => '',
                'label' => $attributeCode,
                'scope' => '',
                'name' => $attributeCode,
            ];
        }
        return $imageTypes;
    }

    /**
     * @return array
     */
    public function getMediaAttributes()
    {
        static $simple;
        if (empty($simple)) {
            $simple = $this->productFactory->create()->setTypeId(Type::TYPE_SIMPLE)->getMediaAttributes();
        }
        return $simple;
    }
}
