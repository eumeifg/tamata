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
namespace Magedelight\Vendor\Block\Sellerhtml\Dashboard;

class Graph extends \Magento\Framework\View\Element\Template
{
    /**
     * Api URL
     */
    const API_URL = 'https://image-charts.com/chart';

    /**
     * All series
     *
     * @var array
     */
    protected $_allSeries = [];

    /**
     * Axis labels
     *
     * @var array
     */
    protected $_axisLabels = [];

    /**
     * Axis maps
     *
     * @var array
     */
    protected $_axisMaps = [];

    /**
     * Data rows
     *
     * @var array
     */
    protected $_dataRows = [];

    /**
     * Simple encoding chars
     *
     * @var string
     */
    protected $_simpleEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Extended encoding chars
     *
     * @var string
     */
    protected $_extendedEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';

    /**
     * Chart width
     *
     * @var string
     */
    protected $_width = '640';

    /**
     * Chart height
     *
     * @var string
     */
    protected $_height = '320';

    /**
     * Google chart api data encoding
     *
     * @var string
     */
    protected $_encoding = 'e';

    /**
     * Html identifier
     *
     * @var string
     */
    protected $_htmlId = '';

    /**
     * @var string
     */
    protected $_template = 'dashboard/graph.phtml';

    /**
     * Adminhtml dashboard data
     *
     * @var \Magedelight\Vendor\Helper\Dashboard\Data
     */
    protected $_dashboardData = null;

    /**
     * @var \Magedelight\Vendor\Helper\Dashboard\Order
     */
    protected $_dataHelper = null;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Sales\Model\ResourceModel\Reports\Order\CollectionFactory $collectionFactory
     * @param \Magedelight\Vendor\Helper\Dashboard\Data $dashboardData
     * @param \Magedelight\Vendor\Helper\Dashboard\Order $_dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Sales\Model\ResourceModel\Reports\Order\CollectionFactory $collectionFactory,
        \Magedelight\Vendor\Helper\Dashboard\Data $dashboardData,
        \Magedelight\Vendor\Helper\Dashboard\Order $_dataHelper,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_dashboardData = $dashboardData;
        $this->_dataHelper = $_dataHelper;
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_prepareData();
    }

    /**
     * Set data rows
     *
     * @param array $rows
     * @return void
     */
    public function setDataRows($rows)
    {
        $this->_dataRows = (array)$rows;
    }

    /**
     * Add series
     *
     * @param string $seriesId
     * @param array $options
     * @return void
     */
    public function addSeries($seriesId, array $options)
    {
        $this->_allSeries[$seriesId] = $options;
    }

    /**
     * Get series
     *
     * @param string $seriesId
     * @return array|false
     */
    public function getSeries($seriesId)
    {
        if (isset($this->_allSeries[$seriesId])) {
            return $this->_allSeries[$seriesId];
        } else {
            return false;
        }
    }

    /**
     * Get all series
     *
     * @return array
     */
    public function getAllSeries()
    {
        return $this->_allSeries;
    }

    /**
     * Get chart url
     *
     * @param bool $directUrl
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @throws \Exception
     */
    public function getChartUrl($directUrl = true)
    {
        $params = [
            'cht' => 'lc',
            'chls' => '7',
            'chf'  => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
            'chm'  => 'B,f4d4b2,0,0,0',
            'chco' => 'db4814',
            'chxs' => '0,0,11|1,0,11',
            'chma' => '15,15,15,15'
        ];

        $this->_allSeries = $this->getRowsData($this->_dataRows);

        foreach ($this->_axisMaps as $axis => $attr) {
            $this->setAxisLabels($axis, $this->getRowsData($attr, true));
        }

        $timezoneLocal = $this->_localeDate->getConfigTimezone();

        /** @var \DateTime $dateStart */
        /** @var \DateTime $dateEnd */
        list($dateStart, $dateEnd) = $this->_collectionFactory->create()->getDateRange(
            $this->getDataHelper()->getParam('period'),
            '',
            '',
            true
        );

        $dateStart->setTimezone(new \DateTimeZone($timezoneLocal));
        $dateEnd->setTimezone(new \DateTimeZone($timezoneLocal));

        if ($this->getDataHelper()->getParam('period') == '24h') {
            $dateEnd->modify('-1 hour');
        } else {
            $dateEnd->setTime(23, 59, 59);
            $dateStart->setTime(0, 0, 0);
        }

        $dates = [];
        $datas = [];

        while ($dateStart <= $dateEnd) {
            switch ($this->getDataHelper()->getParam('period')) {
                case '7d':
                case '1m':
                    $d = $dateStart->format('Y-m-d');
                    $dateStart->modify('+1 day');
                    break;
                case '1y':
                case '2y':
                    $d = $dateStart->format('Y-m');
                    $dateStart->modify('+1 month');
                    break;
                default:
                    $d = $dateStart->format('Y-m-d H:00');
                    $dateStart->modify('+1 hour');
            }
            foreach ($this->getAllSeries() as $index => $serie) {
                if (in_array($d, $this->_axisLabels['x'])) {
                    $datas[$index][] = (double)array_shift($this->_allSeries[$index]);
                } else {
                    $datas[$index][] = 0;
                }
            }
            $dates[] = $d;
        }

        /**
         * setting skip step
         */
        if (count($dates) > 8 && count($dates) < 15) {
            $c = 1;
        } else {
            if (count($dates) >= 15) {
                $c = 2;
            } else {
                $c = 0;
            }
        }
        /**
         * skipping some x labels for good reading
         */
        $i = 0;
        foreach ($dates as $k => $d) {
            if ($i == $c) {
                $dates[$k] = $d;
                $i = 0;
            } else {
                $dates[$k] = '';
                $i++;
            }
        }

        $this->_axisLabels['x'] = $dates;
        $this->_allSeries = $datas;

        // Image-Charts Awesome data format values
        $params['chd'] = "a:";
        $dataDelimiter = ",";
        $dataSetdelimiter = "|";
        $dataMissing = "_";

        // process each string in the array, and find the max length
        $localmaxvalue = [0];
        $localminvalue = [0];
        foreach ($this->getAllSeries() as $index => $serie) {
            $localmaxvalue[$index] = max($serie);
            $localminvalue[$index] = min($serie);
        }

        $maxvalue = max($localmaxvalue);
        $minvalue = min($localminvalue);

        // default values
        $yLabels = [];
        $miny = 0;
        $maxy = 0;
        $yorigin = 0;

        if ($minvalue >= 0 && $maxvalue >= 0) {
            if ($maxvalue > 10) {
                $p = pow(10, $this->_getPow((int) $maxvalue));
                $maxy = ceil($maxvalue / $p) * $p;
                $yLabels = range($miny, $maxy, $p);
            } else {
                $maxy = ceil($maxvalue + 1);
                $yLabels = range($miny, $maxy, 1);
            }
            $yorigin = 0;
        }

        $chartdata = [];

        foreach ($this->getAllSeries() as $index => $serie) {
            $thisdataarray = $serie;
            $count = count($thisdataarray);
            for ($j = 0; $j < $count; $j++) {
                $currentvalue = $thisdataarray[$j];
                if (is_numeric($currentvalue)) {
                    $ylocation = $yorigin + $currentvalue;
                    $chartdata[] = $ylocation . $dataDelimiter;
                } else {
                    $chartdata[] = $dataMissing . $dataDelimiter;
                }
            }
            $chartdata[] = $dataSetdelimiter;
        }
        $buffer = implode('', $chartdata);

        $buffer = rtrim($buffer, $dataSetdelimiter);
        $buffer = rtrim($buffer, $dataDelimiter);
        $buffer = str_replace($dataDelimiter . $dataSetdelimiter, $dataSetdelimiter, $buffer);

        $params['chd'] .= $buffer;

        $valueBuffer = [];

        if (count($this->_axisLabels) > 0) {
            $params['chxt'] = implode(',', array_keys($this->_axisLabels));
            $indexid = 0;
            foreach ($this->_axisLabels as $idx => $labels) {
                if ($idx == 'x') {
                    $this->formatAxisLabelDate((string) $idx, (string) $timezoneLocal);
                    $tmpstring = implode('|', $this->_axisLabels[$idx]);
                    $valueBuffer[] = $indexid . ":|" . $tmpstring;
                } elseif ($idx == 'y') {
                    $valueBuffer[] = $indexid . ":|" . implode('|', $yLabels);
                }
                $indexid++;
            }
            $params['chxl'] = implode('|', $valueBuffer);
        }

        // chart size
        $params['chs'] = $this->getWidth() . 'x' . $this->getHeight();

        // return the encoded data
        if ($directUrl) {
            $p = [];
            foreach ($params as $name => $value) {
                $p[] = $name . '=' . urlencode($value);
            }
            return (string) self::API_URL . '?' . implode('&', $p);
        }
        $gaData = urlencode(base64_encode(json_encode($params)));
        $gaHash = $this->_dashboardData->getChartDataHash($gaData);
        $params = ['ga' => $gaData, 'h' => $gaHash];
        return $this->getUrl('rbvendor/*/tunnel', ['_query' => $params]);
    }

    /**
     * Get rows data
     *
     * @param array $attributes
     * @param bool $single
     * @return array
     */
    protected function getRowsData($attributes, $single = false)
    {
        $items = $this->getCollection()->getItems();
        $options = [];
        foreach ($items as $item) {
            if ($single) {
                $options[] = max(0, $item->getData($attributes));
            } else {
                foreach ((array)$attributes as $attr) {
                    $options[$attr][] = max(0, $item->getData($attr));
                }
            }
        }
        return $options;
    }

    /**
     * Set axis labels
     *
     * @param string $axis
     * @param array $labels
     * @return void
     */
    public function setAxisLabels($axis, $labels)
    {
        $this->_axisLabels[$axis] = $labels;
    }

    /**
     * Set html id
     *
     * @param string $htmlId
     * @return void
     */
    public function setHtmlId($htmlId)
    {
        $this->_htmlId = $htmlId;
    }

    /**
     * Get html id
     *
     * @return string
     */
    public function getHtmlId()
    {
        return $this->_htmlId;
    }

    /**
     * Return pow
     *
     * @param int $number
     * @return int
     */
    protected function _getPow($number)
    {
        $pow = 0;
        while ($number >= 10) {
            $number = $number / 10;
            $pow++;
        }
        return $pow;
    }

    /**
     * Return chart width
     *
     * @return string
     */
    protected function getWidth()
    {
        return $this->_width;
    }

    /**
     * Return chart height
     *
     * @return string
     */
    protected function getHeight()
    {
        return $this->_height;
    }

    /**
     * Prepare chart data
     *
     * @return void
     */
    protected function _prepareData()
    {
        if ($this->_dataHelper !== null) {
            $availablePeriods = array_keys($this->_dashboardData->getDatePeriods());
            $period = ($this->getRequest()->getParam('period')) ?: '2y';
            $this->getDataHelper()->setParam(
                'period',
                $period && in_array($period, $availablePeriods) ? $period : '24h'
            );
        }
    }

    /**
     * @return array|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|\Magento\Eav\Model\Entity\Collection\Abstract
     */
    public function getCollection()
    {
        return $this->getDataHelper()->getCollection();
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->getDataHelper()->getCount();
    }

    /**
     * Get data helper
     *
     * @return \Magedelight\Vendor\Helper\Dashboard\Order
     */
    public function getDataHelper()
    {
        return $this->_dataHelper;
    }

    /**
     * @param $x
     * @param $y
     */
    public function setAxisMaps($x, $y)
    {
        $this->_axisMaps = ['x' => $x, 'y' => $y];
    }

    /**
     * @param $idx
     * @param $timezoneLocal
     * @throws \Exception
     */
    private function formatAxisLabelDate($idx, $timezoneLocal)
    {
        foreach ($this->_axisLabels[$idx] as $_index => $_label) {
            if ($_label != '') {
                $period = new \DateTime($_label, new \DateTimeZone($timezoneLocal));
                switch ($this->getDataHelper()->getParam('period')) {
                    case '24h':
                        $this->_axisLabels[$idx][$_index] = $this->_localeDate->formatDateTime(
                            $period->setTime((int) $period->format('H'), 0, 0),
                            \IntlDateFormatter::NONE,
                            \IntlDateFormatter::SHORT
                        );
                        break;
                    case '7d':
                    case '1m':
                        $this->_axisLabels[$idx][$_index] = $this->_localeDate->formatDateTime(
                            $period,
                            \IntlDateFormatter::SHORT,
                            \IntlDateFormatter::NONE
                        );
                        break;
                    case '1y':
                    case '2y':
                        $this->_axisLabels[$idx][$_index] = date('m/Y', strtotime($_label));
                        break;
                }
            } else {
                $this->_axisLabels[$idx][$_index] = '';
            }
        }
    }
}
