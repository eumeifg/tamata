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
namespace Magedelight\Vendor\Helper\Dashboard;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Data helper for dashboard
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    protected $_stores;

    /**
     * @var string
     */
    protected $_installDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DeploymentConfig $deploymentConfig
    ) {
        parent::__construct(
            $context
        );
        $this->_installDate = $deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE);
        $this->_storeManager = $storeManager;
    }

    /**
     * Retrieve stores configured in system.
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStores()
    {
        if (!$this->_stores) {
            $this->_stores = $this->_storeManager->getStore()->getResourceCollection()->load();
        }
        return $this->_stores;
    }

    /**
     * Retrieve number of loaded stores
     *
     * @return int
     */
    public function countStores()
    {
        return sizeof($this->_stores->getItems());
    }

    /**
     * Prepare array with periods for dashboard graphs
     *
     * @return array
     */
    public function getDatePeriods()
    {
        return [
            '24h' => __('Last 24 Hours'),
            '7d' => __('Last 7 Days'),
            '1m' => __('Current Month'),
            '1y' => __('YTD'),
            '2y' => __('2YTD')
            ];
    }

    /**
     * Create data hash to ensure that we got valid
     * data and it is not changed by some one else.
     *
     * @param string $data
     * @return string
     */
    public function getChartDataHash($data)
    {
        $secret = $this->_installDate;
        return sha1($data . $secret);
    }
}
