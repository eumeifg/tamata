<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Api;

use Magento\Framework\Api\SearchCriteriaInterface;


interface KtplPushnotificationsRepositoryInterface
{

    /**
     * Save ktpl_pushnotifications
     * @param \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface $ktplPushnotifications
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface $ktplPushnotifications
    );

    /**
     * Retrieve ktpl_pushnotifications
     * @param string $ktplPushnotificationsId
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($ktplPushnotificationsId);

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
     * @param \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface $ktplPushnotifications
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface $ktplPushnotifications
    );

    /**
     * Delete ktpl_pushnotifications by ID
     * @param string $ktplPushnotificationsId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ktplPushnotificationsId);
}

