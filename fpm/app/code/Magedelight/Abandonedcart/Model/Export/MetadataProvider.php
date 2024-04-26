<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace  Magedelight\Abandonedcart\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetadataProvider extends \Magento\Ui\Model\Export\MetadataProvider
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * @var array
     */
    protected $data;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param StoreManagerInterface $storeManager
     * @param string $dateFormat
     * @param array $data
     */
    
    public function __construct(
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        StoreManagerInterface $storeManager,
        $dateFormat = 'M j, Y h:i:s A',
        array $data = []
    ) {
        $this->filter = $filter;
        $this->localeDate = $localeDate;
        $this->locale = $localeResolver->getLocale();
        $this->dateFormat = $dateFormat;
        $this->data = $data;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     *
     * @return string[]
     */
    public function getRowData(DocumentInterface $document, $fields, $options): array
    {
        $row = [];
        foreach ($fields as $column) {
            if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = '';
                }
            } else {
                if ($column == "website_id") {
                    $list = $this->storeManager->getWebsites();

                    $options = [];
                    foreach ($list as $websiteId => $website) {
                        $options[] = [
                               'value'=>$websiteId,
                               'label'=>$website->getName()
                        ];
                    }
                        if(isset($options[0]['label'])){
                            $options = [];
                            foreach ($list as $websiteId => $website) {
                                $options[] = [
                                       'value'=>$websiteId,
                                       'label'=>$website->getName()
                                ];
                            }
                            $row[] = $options[0]['label']; 
                        }else{
                            $row[] = $list[$document->getCustomAttribute($column)->getValue()]->getName();
                        }
                    
                } else {
                    $row[] = $document->getCustomAttribute($column)->getValue();
                }
            }
        }
        return $row;
    }
}
