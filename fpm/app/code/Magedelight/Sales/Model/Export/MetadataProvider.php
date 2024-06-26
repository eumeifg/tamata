<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

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
    
    const VENDOR_STATUS_PENDING = 'Pending';
    const VENDOR_STATUS_ACTIVE = 'Active';
    const VENDOR_STATUS_INACTIVE = 'Inactive';
    const VENDOR_STATUS_DISAPPROVED = 'Disapproved';
    const VENDOR_STATUS_VACATION_MODE = 'On Vacation';
    const VENDOR_STATUS_CLOSED = 'Closed';
    
    /**
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param string $dateFormat
     * @param array $data
     */
    public function __construct(
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        $dateFormat = 'M j, Y H:i:s A',
        array $data = []
    ) {
        $this->filter = $filter;
        $this->localeDate = $localeDate;
        $this->locale = $localeResolver->getLocale();
        $this->dateFormat = $dateFormat;
        $this->data = $data;
    }

    /**
     * Returns Columns component
     *
     * @param UiComponentInterface $component
     * @return UiComponentInterface
     * @throws \Exception
     */
    protected function getColumnsComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $childComponent) {
            if ($childComponent instanceof Columns) {
                return $childComponent;
            }
        }
        throw new \Exception('No columns found');
    }

    /**
     * Returns columns list
     *
     * @param UiComponentInterface $component
     * @return UiComponentInterface[]
     */
    protected function getColumns(UiComponentInterface $component)
    {
        if (!isset($this->columns[$component->getName()])) {
            $columns = $this->getColumnsComponent($component);
            foreach ($columns->getChildComponents() as $column) {
                if ($column->getData('config/label') && $column->getData('config/dataType') !== 'actions') {
                    $this->columns[$component->getName()][$column->getName()] = $column;
                }
            }
        }
        return $this->columns[$component->getName()];
    }

    /**
     * Retrieve Headers row array for Export
     *
     * @param UiComponentInterface $component
     * @return string[]
     */
    public function getHeaders(UiComponentInterface $component)
    {
        $row = [];
        foreach ($this->getColumns($component) as $column) {
            $row[] = $column->getData('config/label');
        }
        return $row;
    }

    /**
     * Returns DB fields list
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function getFields(UiComponentInterface $component)
    {
        $row = [];
        foreach ($this->getColumns($component) as $column) {
            $row[] = $column->getName();
        }
        return $row;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function getRowData(DocumentInterface $document, $fields, $options)
    {
        $row = [];
        $key = array_search('status', $fields);
        foreach ($fields as $column) {
            if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = '';
                }
            } else {
                $row[] = $document->getCustomAttribute($column)->getValue();
                if ($column == 'status') {
                    switch ($row[$key]) {
                        case 0:
                            $row[$key] = self::VENDOR_STATUS_PENDING;
                            break;
                        case 1:
                            $row[$key] = self::VENDOR_STATUS_ACTIVE;
                            break;
                        case 2:
                            $row[$key] = self::VENDOR_STATUS_INACTIVE;
                            break;
                        case 3:
                            $row[$key] = self::VENDOR_STATUS_DISAPPROVED;
                            break;
                        case 4:
                            $row[$key] = self::VENDOR_STATUS_VACATION_MODE;
                            break;
                        case 5:
                            $row[$key] = self::VENDOR_STATUS_CLOSED;
                            break;
                    }
                }
            }
        }
        return $row;
    }

    /**
     * Returns complex option
     *
     * @param array $list
     * @param string $label
     * @param array $output
     * @return void
     */
    protected function getComplexLabel($list, $label, &$output)
    {
        foreach ($list as $item) {
            if (!is_array($item['value'])) {
                $output[$item['value']] = $label . $item['label'];
            } else {
                $this->getComplexLabel($item['value'], $label . $item['label'], $output);
            }
        }
    }

    /**
     * Returns array of Select options
     *
     * @param Select $filter
     * @return array
     */
    protected function getFilterOptions(Select $filter)
    {
        $options = [];
        foreach ($filter->getData('config/options') as $option) {
            if (!is_array($option['value'])) {
                $options[$option['value']] = $option['label'];
            } else {
                $this->getComplexLabel(
                    $option['value'],
                    $option['label'],
                    $options
                );
            }
        }
        return $options;
    }

    /**
     * Returns Filters with options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        $component = $this->filter->getComponent();
        $childComponents = $component->getChildComponents();
        $listingTop = $childComponents['listing_top'];
        foreach ($listingTop->getChildComponents() as $child) {
            if ($child instanceof Filters) {
                foreach ($child->getChildComponents() as $filter) {
                    if ($filter instanceof Select) {
                        $options[$filter->getName()] = $this->getFilterOptions($filter);
                    }
                }
            }
        }
        return $options;
    }

    /**
     * Convert document date(UTC) fields to default scope specified
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface $document
     * @param string $componentName
     * @return void
     */
    public function convertDate($document, $componentName)
    {
        if (!isset($this->data[$componentName])) {
            return;
        }
        foreach ($this->data[$componentName] as $field) {
            $fieldValue = $document->getData($field);
            if (!$fieldValue) {
                continue;
            }
            $convertedDate = $this->localeDate->date(
                new \DateTime($fieldValue, new \DateTimeZone('UTC')),
                $this->locale,
                true
            );
            $document->setData($field, $convertedDate->format($this->dateFormat));
        }
    }
}
