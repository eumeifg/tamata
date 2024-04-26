<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Model;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;
use Magento\Framework\Phrase;

/**
 * Backend Auth model
 */
class Auth
{
    /**
     * @var \Magedelight\Backend\Model\Auth\StorageInterface
     */
    protected $_authStorage;

    /**
     * @var \Magedelight\Backend\Model\Auth\Credential\StorageInterface
     */
    protected $_credentialStorage;

    /**
     * Backend data
     *
     * @var \Magedelight\Backend\Helper\Data
     */
    protected $_sellerBackendData;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var \Magento\Framework\Data\Collection\ModelFactory
     */
    protected $_modelFactory;

    /**
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magedelight\Backend\Helper\Data $sellerBackendData
     * @param \Magedelight\Backend\Model\Auth\StorageInterface $authStorage
     * @param \Magedelight\Backend\Model\Auth\Credential\StorageInterface $credentialStorage
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Framework\Data\Collection\ModelFactory $modelFactory
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magedelight\Backend\Helper\Data $sellerBackendData,
        \Magedelight\Backend\Model\Auth\StorageInterface $authStorage,
        \Magedelight\Backend\Model\Auth\Credential\StorageInterface $credentialStorage,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Framework\Data\Collection\ModelFactory $modelFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_sellerBackendData = $sellerBackendData;
        $this->_authStorage = $authStorage;
        $this->_credentialStorage = $credentialStorage;
        $this->_coreConfig = $coreConfig;
        $this->_modelFactory = $modelFactory;
    }

    /**
     * Set auth storage if it is instance of \Magedelight\Backend\Model\Auth\StorageInterface
     *
     * @param \Magedelight\Backend\Model\Auth\StorageInterface $storage
     * @return $this
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function setAuthStorage($storage)
    {
        if (!$storage instanceof \Magedelight\Backend\Model\Auth\StorageInterface) {
            self::throwException(__('Authentication storage is incorrect.'));
        }
        $this->_authStorage = $storage;
        return $this;
    }

    /**
     * Return auth storage.
     * If auth storage was not defined outside - returns default object of auth storage
     *
     * @return \Magedelight\Backend\Model\Auth\StorageInterface
     * @codeCoverageIgnore
     */
    public function getAuthStorage()
    {
        return $this->_authStorage;
    }

    /**
     * Return current (successfully authenticated) user,
     * an instance of \Magedelight\Backend\Model\Auth\Credential\StorageInterface
     *
     * @return \Magedelight\Backend\Model\Auth\Credential\StorageInterface
     */
    public function getUser()
    {
        return $this->getAuthStorage()->getUser();
    }

    /**
     * Initialize credential storage from configuration
     *
     * @return void
     */
    protected function _initCredentialStorage()
    {
        $this->_credentialStorage = $this->_modelFactory->create(
            'Magedelight\Backend\Model\Auth\Credential\StorageInterface'
        );
    }

    /**
     * Return credential storage object
     *
     * @return null|\Magedelight\Backend\Model\Auth\Credential\StorageInterface
     * @codeCoverageIgnore
     */
    public function getCredentialStorage()
    {
        return $this->_credentialStorage;
    }

    /**
     * Perform login process
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
        }

        try {
            $this->_initCredentialStorage();
            $this->getCredentialStorage()->login($username, $password);
        
            if ($this->getCredentialStorage()->getId()) {
                $this->getAuthStorage()->setUser($this->getCredentialStorage());
                $this->setExtraParams();
                $this->getAuthStorage()->processLogin();
            }

            if (!$this->getAuthStorage()->getUser()) {
                self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
            }
        } catch (PluginAuthenticationException $e) {
            throw $e;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            self::throwException(
                __($e->getMessage()? : 'You did not sign in correctly or your account is temporarily disabled.')
            );
        }
    }
    
    /**
     * Set extram params used in vendor panel grids.
     * return void()
     */
    protected function setExtraParams()
    {
        
        $params = [
            "grid_session" => [
                'product_live' => [
                    "q" => ""
                ],
                'product_nonlive_approved' => [
                    "q" => ""
                ],
                'product_nonlive_pending' => [
                    "q" => ""
                ],
                'product_nonlive_disaaproved' => [
                    "q" => ""
                ],
                'orders_active_new' => [
                    "q" => "",
                    "sfrm" => "new",
                    "sort_order" => "rvo_created_at",
                    "dir" => "DESC",
                    "pdate_from" => "",
                    "pdate_to" => "",
                    "grndt_from" => "",
                    "grndt_to" => "",
                    "status" => "",
                    "increment_id" => "",
                    "bill_to_name" => "",
                    "ship_to_name" => ""
                ],
                'orders_active_confirm' => [
                    "q" => "",
                    "sfrm" => "confirmed",
                    "sort_order" => "rvo_created_at",
                    "dir" => "DESC",
                    "pdate_from" => "",
                    "pdate_to" => "",
                    "grndt_from" => "",
                    "grndt_to" => "",
                    "status" => "",
                    "increment_id" => "",
                    "bill_to_name" => "",
                    "ship_to_name" => ""
                ],
                'orders_active_pack' => [
                    "q" => "",
                    "sfrm" => "packed",
                    "sort_order" => "rvo_created_at",
                    "dir" => "DESC",
                    "pdate_from" => "",
                    "pdate_to" => "",
                    "grndt_from" => "",
                    "grndt_to" => "",
                    "status" => "",
                    "increment_id" => "",
                    "bill_to_name" => "",
                    "ship_to_name" => ""
                ],
                'orders_active_handover' => [
                    "q" => "",
                    "sfrm" => "handover",
                    "sort_order" => "rvo_created_at",
                    "dir" => "DESC",
                    "pdate_from" => "",
                    "pdate_to" => "",
                    "grndt_from" => "",
                    "grndt_to" => "",
                    "status" => "",
                    "increment_id" => "",
                    "bill_to_name" => "",
                    "ship_to_name" => ""
                ],
                'orders_active_intransit' => [
                    "q" => "",
                    "sfrm" => "intransit",
                    "sort_order" => "rvo_created_at",
                    "dir" => "DESC",
                    "pdate_from" => "",
                    "pdate_to" => "",
                    "grndt_from" => "",
                    "grndt_to" => "",
                    "status" => "",
                    "increment_id" => "",
                    "bill_to_name" => "",
                    "ship_to_name" => ""
                ],
                'orders_closed' => [
                    "q" => "",
                    "sfrm" => "closed",
                    "sort_order" => "rvo_created_at",
                    "dir" => "DESC",
                    "pdate_from" => "",
                    "pdate_to" => "",
                    "grndt_from" => "",
                    "grndt_to" => "",
                    "cf_from" => "",
                    "cf_to" => "",
                    "status" => "",
                    "increment_id" => "",
                    "bill_to_name" => "",
                    "ship_to_name" => ""
                ],
                'orders_cancel' => [
                    "q" => "",
                    "sfrm" => "cancelled",
                    "sort_order" => "rvo_created_at",
                    "dir" => "DESC",
                    "pdate_from" => "",
                    "pdate_to" => "",
                    "grndt_from" => "",
                    "grndt_to" => "",
                    "cf_from" => "",
                    "cf_to" => "",
                    "status" => "",
                    "increment_id" => "",
                    "bill_to_name" => "",
                    "ship_to_name" => ""
                ],
                'returns_request' => [
                    "q" => "",
                    "purchaseddate[from]" => "",
                    "purchaseddate[to]" => "",
                    "status" => "",
                    "increment_id" => "",
                    "rma_id" => ""
                ],
                'orders_complete' => [
                    "q" => "",
                    "sfrm" => "complete",
                    "sort_order" => "rvo_created_at",
                    "dir" => "DESC",
                    "pdate_from" => "",
                    "pdate_to" => "",
                    "grndt_from" => "",
                    "grndt_to" => "",
                    "status" => "",
                    "increment_id" => "",
                    "bill_to_name" => "",
                    "ship_to_name" => ""
                ],
                'financial_transaction_summary' => [
                    "q" => "",
                    "commision[from]" => "",
                    "commision[to]" => "",
                    "marketplace_fee[from]" => "",
                    "marketplace_fee[to]" => "",
                    "cf[from]" => "",
                    "cf[to]" => "",
                    "service_tax[from]" => "",
                    "service_tax[to]" => "",
                    "net_payable[from]" => "",
                    "net_payable[to]" => "",
                    "created_date[from]" => "",
                    "created_date[to]" => "",
                    "status" => "",
                    "increment_id" => "",
                ],
                'financial_commision_invoice' => [
                    "q" => "",
                    "invoice_genereated_date[from]" => "",
                    "invoice_genereated_date[to]" => "",
                    "consolidation_invoice_number" => "",
                    "total_fees_amount[from]" => "",
                    "total_fees_amount[to]" => ""
                ],
            ]
        ];
        
        $this->getAuthStorage()->setGridSession($params);
    }

    /**
     * Perform logout process
     *
     * @return void
     */
    public function logout()
    {
        $this->getAuthStorage()->processLogout();
    }

    /**
     * Check if current user is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->getAuthStorage()->isLoggedIn();
    }

    /**
     * Throws specific Backend Authentication \Exception
     *
     * @param \Magento\Framework\Phrase $msg
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @static
     */
    public static function throwException(Phrase $msg = null)
    {
        if ($msg === null) {
            $msg = __('Authentication error occurred.');
        }
        throw new AuthenticationException($msg);
    }
}
