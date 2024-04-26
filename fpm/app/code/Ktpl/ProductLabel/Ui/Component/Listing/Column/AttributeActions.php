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

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class AttributeActions extends Column
{
    const URL_PATH_EDIT = 'ktpl_productlabel/productlabel/edit';
    const URL_PATH_DELETE = 'ktpl_productlabel/productlabel/delete';

    protected $urlBuilder;

    protected $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper    = $escaper;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if($item['option_id'] == 1){
                    $item['option_label'] = 'Yes';
                }

                if (isset($item['product_label_id'])) {
                    $name                         = $this->escaper->escapeHtml($item['name']);
                    $item[$this->getData('name')] = [
                        'edit'   => [
                            'href'  => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                ['product_label_id' => $item['product_label_id']]
                            ),
                            'label' => __('Edit'),
                        ],
                        'delete' => [
                            'href'    => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                ['product_label_id' => $item['product_label_id']]
                            ),
                            'label'   => __('Delete'),
                            'confirm' => [
                                'title'   => __('Delete %1', $name),
                                'message' => __('Are you sure you want to delete the "%1" product label?', $name),
                            ],
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
