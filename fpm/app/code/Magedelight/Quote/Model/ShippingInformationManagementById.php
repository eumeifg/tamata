<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Quote
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Quote\Model;

use Magento\Authorization\Model\UserContextInterface as UserContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingInformationManagementById implements \Magedelight\Quote\Api\ShippingInformationManagementByIdInterface
{
    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var PaymentDetailsFactory
     */
    protected $paymentDetailsFactory;

    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var QuoteAddressValidator
     */
    protected $addressValidator;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     * @deprecated 100.2.0
     */
    protected $addressRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @deprecated 100.2.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     * @deprecated 100.2.0
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var \Magento\Quote\Model\ShippingAssignmentFactory
     */
    protected $shippingAssignmentFactory;

    /**
     * @var \Magento\Quote\Model\ShippingFactory
     */
    private $shippingFactory;

    protected $_customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\QuoteAddressValidator $addressValidator
     * @param Logger $logger
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param CartExtensionFactory|null $cartExtensionFactory
     * @param ShippingAssignmentFactory|null $shippingAssignmentFactory
     * @param ShippingFactory|null $shippingFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteAddressValidator $addressValidator,
        UserContext $userContext,
        Logger $logger,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        AddressInterface $addressInterface,
        BillingAddressManagementInterface $billingAddressManagement,
        CartExtensionFactory $cartExtensionFactory = null,
        ShippingAssignmentFactory $shippingAssignmentFactory = null,
        ShippingFactory $shippingFactory = null
    ) {
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->quoteRepository = $quoteRepository;
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->_request = $request;
        $this->userContext = $userContext;
        $this->_customerRepository = $customerRepository;
        $this->addressInterface = $addressInterface;
        $this->addressRepository = $addressRepository;
        $this->billingAddressManagement = $billingAddressManagement;
        $this->scopeConfig = $scopeConfig;
        $this->totalsCollector = $totalsCollector;
        $this->cartExtensionFactory = $cartExtensionFactory ?: ObjectManager::getInstance()
            ->get(CartExtensionFactory::class);
        $this->shippingAssignmentFactory = $shippingAssignmentFactory ?: ObjectManager::getInstance()
            ->get(ShippingAssignmentFactory::class);
        $this->shippingFactory = $shippingFactory ?: ObjectManager::getInstance()
            ->get(ShippingFactory::class);
    }

    /**
     * Save address information.
     *
     * @param int $cartId
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function saveAddressInformation(
        $cartId
    ) {
        // Get All Params i.e address_id, method
        $request = $this->_request->getParams();
        if (empty($request['addressId']) || empty($request['method']) || empty($request['carrier'])) {
            throw new InputException(
                __("Please select address & shipping method.")
            );
        }        

        /* get Address by address id */
        $selectedShippingAddress = $this->addressRepository->getById($request['addressId']);
       /* get Address by address id */
        
            $this->addressInterface->setFirstname($selectedShippingAddress->getFirstname());
            $this->addressInterface->setLastname($selectedShippingAddress->getLastname());
            $this->addressInterface->setCity($selectedShippingAddress->getCity());
            $this->addressInterface->setPostcode($selectedShippingAddress->getPostcode());
            $this->addressInterface->setTelephone($selectedShippingAddress->getTelephone());
            $this->addressInterface->setCountryId($selectedShippingAddress->getCountryId());
            $this->addressInterface->setStreet($selectedShippingAddress->getStreet());
            $region = $selectedShippingAddress->getRegion();
            if (is_object($region)) {
                $region = '';
            }
            $this->addressInterface->setRegion($region);
            $this->addressInterface->setRegionId($selectedShippingAddress->getRegionId());

            $this->addressInterface->setCustomAttributes($selectedShippingAddress->getCustomAttributes()); 

        try {
            $this->billingAddressManagement->assign($cartId, $this->addressInterface, true);
            // Get Current Quote
            $quote = $this->quoteRepository->getActive($cartId);
            $carrierCode = $request['carrier'];
            $methodCode = $request['method'];
            $quote = $this->prepareShippingAssignment(
                $quote,
                $this->addressInterface,
                $carrierCode . '_' . $methodCode
            );
            $quote->setIsMultiShipping(false);

            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(
                __('The shipping information was unable to be saved. Verify the input data and try again.')
            );
        }


        /** @var \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));
        return $paymentDetails;
    }

    /**
     * Validate quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @throws InputException
     * @throws NoSuchEntityException
     * @return void
     */
    protected function validateQuote(\Magento\Quote\Model\Quote $quote)
    {
        if (0 == $quote->getItemsCount()) {
            throw new InputException(
                __("The shipping method can't be set for an empty cart. Add an item to cart and try again.")
            );
        }
    }

    /**
     * Prepare shipping assignment.
     *
     * @param CartInterface $quote
     * @param AddressInterface $address
     * @param string $method
     * @return CartInterface
     */
    private function prepareShippingAssignment(CartInterface $quote, AddressInterface $address, $method)
    {
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }

        $shippingAssignments = $cartExtension->getShippingAssignments();
        if (empty($shippingAssignments)) {
            $shippingAssignment = $this->shippingAssignmentFactory->create();
        } else {
            $shippingAssignment = $shippingAssignments[0];
        }

        $shipping = $shippingAssignment->getShipping();
        if ($shipping === null) {
            $shipping = $this->shippingFactory->create();
        }

        $shipping->setAddress($address);
        $shipping->setMethod($method);
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        return $quote->setExtensionAttributes($cartExtension);
    }
}
