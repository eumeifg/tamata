<?php

namespace Ktpl\Pushnotification\Ui\Component\Status\Column;

// class Options implements \Magento\Framework\Option\ArrayInterface
// {
//     /**
//      * Options getter
//      *
//      * @return array
//      */
//     public function toOptionArray()
//     {
//         return [
//             ['value' => 1, 'label' => __('Enable')],
//             ['value' => 0, 'label' => __('Disable')]
//         ];
//     }
// }

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Options extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                // $items['instock'] is column value
                if ($items['status'] == 1) {
                    $items['status'] = 'Enable';
                } else {
                    $items['status'] = 'Disable';
                }
            }
        }
        return $dataSource;
    }
}