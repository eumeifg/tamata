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
namespace Magedelight\Vendor\Model;

use Magedelight\Vendor\Api\Data\RequestStatusDataInterface;
use Magedelight\Vendor\Api\Data\VendorInterface;
use Magedelight\Vendor\Model\Source\RequestTypes;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Vendor Request Model
 \Magento\Framework\Model\AbstractModel
 */
class Request extends AbstractExtensibleModel implements RequestStatusDataInterface
{

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    protected $_scopeConfig;

    /**
     * @var RequestTypes
     */
    protected $requestTypes;

    /**
     * @var Source\RequestStatuses
     */
    protected $requestStatus;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magedelight\Vendor\Model\ResourceModel\Request $resource
     * @param \Magedelight\Vendor\Model\ResourceModel\Request\Collection $resourceCollection
     * @param array $data
     * @param RequestTypes $requestTypes
     * @param Source\RequestStatuses $requestStatus
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magedelight\Vendor\Model\Source\RequestTypes $requestTypes,
        \Magedelight\Vendor\Model\Source\RequestStatuses $requestStatus,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magedelight\Vendor\Model\ResourceModel\Request $resource = null,
        \Magedelight\Vendor\Model\ResourceModel\Request\Collection $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->requestTypes  = $requestTypes;
        $this->requestStatus = $requestStatus;
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        $this->_scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
    }

    /**
     * Initialize vendor model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Magedelight\Vendor\Model\ResourceModel\Request::class);
    }

    /**
     * @param \Magedelight\Vendor\Api\Data\VendorInterface $vendor
     * @param int $statusRequestType
     * @return Request
     * @throws \Exception
     */
    public function saveVendorStatusRequest(VendorInterface $vendor, $statusRequestType)
    {
        try {
            $this->setData('vendor_id', $vendor->getId());
            $this->setData('request_type', $statusRequestType);
            $this->setData('reason', $vendor->getVacationMessage());
            if ($statusRequestType == RequestTypes::VENDOR_REQUEST_TYPE_VACATION) {
                $this->setData('vacation_from_date', $vendor->getVacationFromDate());
                $this->setData('vacation_to_date', $vendor->getVacationToDate());
            }
            $this->save();
            $this->_eventManager->dispatch(
                'vendor_status_request_save_after',
                ['vendor' => $vendor,'statusRequestType' => $statusRequestType]
            );
        } catch (Exception $e) {
            throw new LocalizedException(__('Your request was not processed, Please try again.'));
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function getVendorId()
    {
        return $this->getData(self::VENDOR_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(self::VENDOR_ID, $vendorId);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestType()
    {
        return $this->getData(self::REQUEST_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestType($reqType)
    {
        return $this->setData(self::REQUEST_TYPE, $reqType);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestTypeText()
    {
        if ($this->getData(self::REQUEST_TYPE_TXT) == null) {
            $this->setRequestTypeText($this->requestTypes->getOptionText($this->getRequestType()));
        }
        return $this->getData(self::REQUEST_TYPE_TXT);
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestTypeText($reqTypeText)
    {
        return $this->setData(self::REQUEST_TYPE_TXT, $reqTypeText);
    }

    /**
     * {@inheritDoc}
     */
    public function getReason()
    {
        return $this->getData(self::REASON);
    }

    /**
     * {@inheritDoc}
     */
    public function setReason($var)
    {
        return $this->setData(self::REASON, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestedAt()
    {
        return $this->getData(self::REQUESTED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestedAt($var)
    {
        return $this->setData(self::REQUESTED_AT, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getApprovedAt()
    {
        return $this->getData(self::APPROVED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setApprovedAt($var)
    {
        return $this->setData(self::APPROVED_AT, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($var)
    {
        return $this->setData(self::STATUS, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusText()
    {
        if ($this->getData(self::STATUS_TEXT) == null) {
            $this->setStatusText($this->requestStatus->getOptionText($this->getStatus()));
        }
        return $this->getData(self::STATUS_TEXT);
    }

    /**
     * {@inheritDoc}
     */
    public function setStatusText($var)
    {
        return $this->setData(self::STATUS_TEXT, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getVacationFromDate()
    {
        return $this->getData(self::VACATION_FROM_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function setVacationFromDate($var)
    {
        return $this->setData(self::VACATION_FROM_DATE, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getVacationToDate()
    {
        return $this->getData(self::VACATION_TO_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function setVacationToDate($var)
    {
        return $this->setData(self::VACATION_TO_DATE, $var);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\RequestStatusDataExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
