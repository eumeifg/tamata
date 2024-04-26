<?php

namespace CAT\GiftCard\Model\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\GiftCardAccount\Api\Exception\TooManyAttemptsException;
use Magento\GiftCardAccount\Model\Spi\GiftCardAccountManagerInterface;
use Magento\GiftCardAccountGraphQl\Model\Money\Formatter as MoneyFormatter;
use Magento\Store\Api\Data\StoreInterface;
use CAT\GiftCard\Helper\Data as GiftCardHelper;

class GiftCardAccount extends \Magento\GiftCardAccountGraphQl\Model\DataProvider\GiftCardAccount
{
    /**
     * @var GiftCardAccountManagerInterface
     */
    private $giftCardAccountManager;

    /**
     * @var MoneyFormatter
     */
    private $moneyFormatter;

    protected $giftCardHelper;

    /**
     * @param GiftCardAccountManagerInterface $giftCardAccountManager
     * @param MoneyFormatter $moneyFormatter
     */
    public function __construct(
        GiftCardAccountManagerInterface $giftCardAccountManager,
        MoneyFormatter $moneyFormatter,
        GiftCardHelper $giftCardHelper
    ) {
        $this->giftCardAccountManager = $giftCardAccountManager;
        $this->moneyFormatter = $moneyFormatter;
        $this->giftCardHelper = $giftCardHelper;
        parent::__construct($giftCardAccountManager, $moneyFormatter);
    }

    public function getByCode(string $giftCardCode, StoreInterface $store): array
    {
        try {
            if ($customGiftCard = $this->giftCardHelper->getCustomGiftCardAccountDetails($giftCardCode, $store)) {
                return $customGiftCard;
            }
            /** @var \Magento\GiftCardAccount\Model\Giftcardaccount $giftCardAccount */
            $giftCardAccount = $this->giftCardAccountManager->requestByCode($giftCardCode, (int)$store->getWebsiteId());
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (TooManyAttemptsException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        } catch (\InvalidArgumentException $e) {
            throw new GraphQlInputException(__('Invalid gift card'), $e);
        }

        return [
            'code' => $giftCardAccount->getCode(),
            'balance' => $this->moneyFormatter->formatAmountAsMoney($giftCardAccount->getBalance(), $store),
            'expiration_date' => $giftCardAccount->getDateExpires(),
            'store_credit' => null
        ];
    }
}
