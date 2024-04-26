<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Api;

use Magento\Framework\Api\SearchCriteriaInterface;


interface KtplAbandonCartRepositoryInterface
{

    /**
     * Save ktpl_pushnotifications
     * @param \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface $ktplPushnotifications
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface $ktplAbandonCart
    );

    /**
     * Retrieve ktpl_pushnotifications
     * @param string $ktplPushnotificationsId
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($ktplAbandonCartId);

    /**
     * Retrieve ktpl_pushnotifications matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete ktpl_pushnotifications
     * @param \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface $ktplAbandonCart
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface $ktplAbandonCart
    );

    /**
     * Delete ktpl_pushnotifications by ID
     * @param string $ktplAbandonCartId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ktplAbandonCartId);
}

