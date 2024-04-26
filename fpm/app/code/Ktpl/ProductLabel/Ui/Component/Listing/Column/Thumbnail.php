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

namespace Ktpl\ProductLabel\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'thumbnail';

    const ALT_FIELD = 'name';

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Ktpl\ProductLabel\Model\ImageLabel\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder  = $urlBuilder;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['image'])) {
                    $item[$fieldName . '_src']      = $this->imageHelper->getBaseUrl() . '/' . $item['image'];
                    $item[$fieldName . '_alt']      = $this->getAlt($item) ?: $this->imageHelper->getLabel();
                    $item[$fieldName . '_link']     = $this->urlBuilder->getUrl(
                        'ktpl_productlabel/productlabel/edit',
                        ['product_label_id' => $item['product_label_id']]
                    );
                    $item[$fieldName . '_orig_src'] = $this->imageHelper->getBaseUrl() . '/' . $item['image'];
                } else {
                    $item[$fieldName . '_src']      = $this->imageHelper->getBaseUrl() . '/' . 'thumbnail.jpg';
                    $item[$fieldName . '_alt']      = $this->getAlt($item) ?: $this->imageHelper->getLabel();
                    $item[$fieldName . '_link']     = $this->urlBuilder->getUrl(
                        'ktpl_productlabel/productlabel/edit',
                        ['product_label_id' => $item['product_label_id']]
                    );
                    $item[$fieldName . '_orig_src'] = $this->imageHelper->getBaseUrl() . '/' . 'thumbnail.jpg';
                }
            }
        }

        return $dataSource;
    }

    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;

        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
