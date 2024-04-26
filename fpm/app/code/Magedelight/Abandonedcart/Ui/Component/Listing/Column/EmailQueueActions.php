<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class EmailQueueActions extends Column
{
    /** Url path */
    const BLOG_URL_PATH_EDIT = 'abandonedcart/emailqueue/edit';
    const BLOG_URL_PATH_DELETE = 'abandonedcart/emailqueue/delete';
    const BLOG_URL_PATH_CANCEL = 'abandonedcart/emailqueue/cancel';
    const BLOG_URL_PATH_EMAIL_PREVIEW = 'abandonedcart/emailqueue/preview';
    const BLOG_URL_PATH_SEND_MANUALLY = 'abandonedcart/emailqueue/sendmanually';

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::BLOG_URL_PATH_EDIT,
        $cancelUrl = self::BLOG_URL_PATH_CANCEL
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
       
                if (isset($item['abandonedcart_email_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['id' => $item['abandonedcart_email_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::BLOG_URL_PATH_DELETE,
                            ['id' => $item['abandonedcart_email_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete'),
                            'message' => __('Are you sure you wan\'t to delete record?')
                        ]
                    ];
                    $item[$name]['cancel'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::BLOG_URL_PATH_CANCEL,
                            ['id' => $item['abandonedcart_email_id']]
                        ),
                        'label' => __('Cancel'),
                        'confirm' => [
                            'title' => __('Cancel'),
                            'message' => __('Are you sure you wan\'t to cancel email sending to this customer?')
                        ]
                    ];
                    $item[$name]['preview'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::BLOG_URL_PATH_EMAIL_PREVIEW,
                            ['id' => $item['abandonedcart_email_id']]
                        ),
                         'class'=>'open-modal-form',
                        'label' => __('Preview')
                    ];
                    $item[$name]['send_now'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::BLOG_URL_PATH_SEND_MANUALLY,
                            ['id' => $item['abandonedcart_email_id']]
                        ),
                        'label' => __('Send Now'),
                        'confirm' => [
                            'title' => __('Send Now'),
                            'message' => __('Are you sure you wan\'t to send email manually to this customer?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
