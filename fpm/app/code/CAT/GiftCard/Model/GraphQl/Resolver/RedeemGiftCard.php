<?php

namespace CAT\GiftCard\Model\GraphQl\Resolver;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftCardAccount\Api\Exception\TooManyAttemptsException;
use Magento\GiftCardAccount\Api\GiftCardRedeemerInterface;
use Magento\GiftCardAccountGraphQl\Model\DataProvider\GiftCardAccount as GiftCardAccountProvider;
use Magento\Store\Model\Store;
use CAT\GiftCard\Helper\Data as GiftCardHelper;
use Magento\CustomerBalance\Model\BalanceFactory;

class RedeemGiftCard extends \Magento\GiftCardAccountGraphQl\Model\Resolver\RedeemGiftCard
{
    /**
     * @var GiftCardRedeemerInterface
     */
    private $giftCardRedeemer;

    /**
     * @var GiftCardAccountProvider
     */
    private $giftCardAccountProvider;

    /**
     * @var GiftCardHelper
     */
    protected $giftCardHelper;

    /**
     * @var BalanceFactory
     */
    protected $_balanceFactory;

    public function __construct(
        GiftCardRedeemerInterface $giftCardRedeemer,
        GiftCardAccountProvider   $gifCardAccountProvider,
        GiftCardHelper            $giftCardHelper,
        BalanceFactory            $balanceFactory
    )
    {
        parent::__construct($giftCardRedeemer, $gifCardAccountProvider);
        $this->giftCardRedeemer = $giftCardRedeemer;
        $this->giftCardAccountProvider = $gifCardAccountProvider;
        $this->giftCardHelper = $giftCardHelper;
        $this->_balanceFactory = $balanceFactory;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $customerId = $context->getUserId();
        if (empty($customerId)) {
            throw new GraphQlAuthorizationException(__('Cannot find the customer to update balance'));
        }

        $giftCardCode = $args['input']['gift_card_code'] ?? '';
        if (empty($giftCardCode)) {
            throw new GraphQlInputException(__("Required parameter '%1' is missing", 'gift_card_code'));
        }
        /** @var Store $store */
        $store = $context->getExtensionAttributes()->getStore();
        /* Validate custom Gifts Cards */
        if ($this->giftCardHelper->validateGiftCardV2($giftCardCode, $customerId)){
            return  $this->giftCardHelper->redeemedGiftCardGetByCode($giftCardCode, $customerId, $store);
        }

        try {
            $this->giftCardRedeemer->redeem($giftCardCode, $customerId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (CouldNotSaveException|TooManyAttemptsException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
        $returnData = $this->giftCardAccountProvider->getByCode($giftCardCode, $store);
        $balance = $this->_balanceFactory->create();
        $balance->setCustomerId($customerId)->loadByCustomer();
        $newData = array_merge($returnData, ['store_credit' => $balance->getAmount()]);
        return $newData;
    }
}
