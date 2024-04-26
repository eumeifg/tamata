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
namespace Magedelight\Vendor\Cron;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magento\Framework\App\ResourceConnection;

/**
 * @author Rocket Bazaar Core Team
 * Created at 12 May, 2016 05:05:27 PM
 */
class VacationScheduler
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * VacationScheduler constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Psr\Log\LoggerInterface $logger
     */

    public function __construct(
        ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_resource = $resource;
        $this->_dateTime = $date;
        $this->logger = $logger;
    }

    /**
     * Retrieve write connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resource->getConnection();
        }
        return $this->_connection;
    }

    /**
     * Add products to changes list with price which depends on date
     *
     * @return void
     */
    public function execute()
    {
        $connection = $this->_getConnection();
        $currentDate = $this->_dateTime->gmtDate("Y-m-d");
        $this->logger->debug('Vendor Vacation Mode :- ' . $currentDate);
        /*
         * Change vendor status to vacation mode
         */
        $connection->update(
            $this->_resource->getTableName('md_vendor_website_data'),
            [
                'status' => VendorStatus::VENDOR_STATUS_VACATION_MODE,
                'vacation_request_status' => null
            ],
            [
                "vacation_request_status=?" => VendorStatus::VENDOR_REQUEST_STATUS_APPROVED,
                "vacation_from_date <= vacation_to_date",
                "vacation_to_date >= ?" => $currentDate,
                "status != ?" => VendorStatus::VENDOR_STATUS_VACATION_MODE
            ]
        );

        /*
         * Change vendor status to active from vacation mode
         */
        $connection->update(
            $this->_resource->getTableName('md_vendor_website_data'),
            [
                'status' => VendorStatus::VENDOR_STATUS_ACTIVE,
                //'vacation_request_status' => null,
                'vacation_from_date' => null,
                'vacation_to_date' => null
            ],
            [
                //"vacation_request_status=?" => VendorStatus::VENDOR_REQUEST_STATUS_APPROVED,
                "vacation_to_date < ?" => $currentDate,
                "status = ?" => VendorStatus::VENDOR_STATUS_VACATION_MODE
            ]
        );

        /*
         * Change vendor vacation request status to null in case request
         *  has not any response from admin and request to date expired.
         */
        $connection->update(
            $this->_resource->getTableName('md_vendor_website_data'),
            [
                'vacation_request_status' => null,
                'vacation_to_date' => null,
                'vacation_from_date' => null
            ],
            [
                "vacation_request_status=?" => VendorStatus::VENDOR_REQUEST_STATUS_PENDING,
                "vacation_to_date < ?" => $currentDate
            ]
        );
    }
}
