<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Ui\StopWord\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Amasty\ElasticSearch\Api\Data\StopWordInterface;

class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = [
                    'edit'   => [
                        'href'  => $this->urlBuilder->getUrl(
                            'amasty_elastic/stopword/edit',
                            [StopWordInterface::STOP_WORD_ID => $item[StopWordInterface::STOP_WORD_ID],]
                        ),
                        'label' => __('Edit'),
                    ],
                    'delete' => [
                        'href'    => $this->urlBuilder->getUrl(
                            'amasty_elastic/stopword/delete',
                            [StopWordInterface::STOP_WORD_ID => $item[StopWordInterface::STOP_WORD_ID]]
                        ),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title' => __('Are you sure you want to delete Stop Word?'),
                        ],
                    ],
                ];
            }
        }

        return $dataSource;
    }
}
