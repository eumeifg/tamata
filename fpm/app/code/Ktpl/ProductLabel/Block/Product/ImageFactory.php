<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Block\Product;

use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\ConfigInterface;

class ImageFactory extends \Magento\Catalog\Block\Product\ImageFactory
{
    /**
     * @var ConfigInterface
     */
    private $presentationConfig;

    /**
     * @var AssetImageFactory
     */
    private $viewAssetImageFactory;

    /**
     * @var ParamsBuilder
     */
    private $imageParamsBuilder;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PlaceholderFactory
     */
    private $viewAssetPlaceholderFactory;

    /**
     * @var string
     */
    private $template;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $presentationConfig,
        AssetImageFactory $viewAssetImageFactory,
        PlaceholderFactory $viewAssetPlaceholderFactory,
        ParamsBuilder $imageParamsBuilder,
        string $template = 'Ktpl_ProductLabel::product/listlabel.phtml'
    ) {
        $this->objectManager               = $objectManager;
        $this->presentationConfig          = $presentationConfig;
        $this->viewAssetPlaceholderFactory = $viewAssetPlaceholderFactory;
        $this->viewAssetImageFactory       = $viewAssetImageFactory;
        $this->imageParamsBuilder          = $imageParamsBuilder;
        $this->template                    = $template;
    }

    public function create(Product $product, string $imageId, array $attributes = null): ImageBlock
    {
        $viewImageConfig = $this->presentationConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            ImageHelper::MEDIA_TYPE_CONFIG_NODE,
            $imageId
        );

        $imageMiscParams  = $this->imageParamsBuilder->build($viewImageConfig);
        $originalFilePath = $product->getData($imageMiscParams['image_type']);

        if ($originalFilePath === null || $originalFilePath === 'no_selection') {
            $imageAsset = $this->viewAssetPlaceholderFactory->create(
                [
                    'type' => $imageMiscParams['image_type'],
                ]
            );
        } else {
            $imageAsset = $this->viewAssetImageFactory->create(
                [
                    'miscParams' => $imageMiscParams,
                    'filePath'   => $originalFilePath,
                ]
            );
        }

        $data = [
            'data' => [
                'template'          => 'Ktpl_ProductLabel::product/listlabel.phtml',
                'image_url'         => $imageAsset->getUrl(),
                'width'             => $imageMiscParams['image_width'],
                'height'            => $imageMiscParams['image_height'],
                'label'             => $this->getLabel($product, $imageMiscParams['image_type']),
                'ratio'             => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
                'product_id'        => $product->getId(),
            ],
        ];

        // Override starts here.
        /**
         * @var \Ktpl\ProductLabel\Block\ProductLabel\ProductLabel $labelsRenderer
        */
        $labelsRenderer = $this->objectManager->create(\Ktpl\ProductLabel\Block\ProductLabel\ProductLabel::class);
        $labelsRenderer->setProduct($product);

        $data['data']['product_labels']               = $labelsRenderer->getProductLabels() ?? [];
        $data['data']['product_labels_wrapper_class'] = $labelsRenderer->getWrapperClass() ?? [];

        /**
         * @var ImageBlock $block
        */
        $block = $this->objectManager->create(ImageBlock::class, $data);

        return $block;
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @param array $attributes Attributes
     *
     * @return string
     */
    private function getStringCustomAttributes(array $attributes): string
    {
        $result = [];
        foreach ($attributes as $name => $value) {
            $result[] = $name . '="' . $value . '"';
        }

        return !empty($result) ? implode(' ', $result) : '';
    }

    /**
     * Calculate image ratio
     *
     * @param int $width  Width
     * @param int $height Height
     *
     * @return float
     */
    private function getRatio(int $width, int $height): float
    {
        if ($width && $height) {
            return $height / $width;
        }

        return 1.0;
    }

    /**
     * @param Product $product   The product
     * @param string  $imageType The image type
     *
     * @return string
     */
    private function getLabel(Product $product, string $imageType): string
    {
        $label = $product->getData($imageType . '_' . 'label');
        if (empty($label)) {
            $label = $product->getName();
        }

        return (string) $label;
    }
}
