<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Api;

use Magento\Framework\Api\SearchCriteriaInterface;


interface KtplDevicetokensRepositoryInterface
{

    /**
     * Save ktpl_devicetokens
     * @param \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface $ktplDevicetokens
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface $ktplDevicetokens
    );

    /**
     * Retrieve ktpl_devicetokens
     * @param string $ktplDevicetokensId
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($ktplDevicetokensId);

    /**
     * Retrieve ktpl_devicetokens matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete ktpl_devicetokens
     * @param \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface $ktplDevicetokens
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface $ktplDevicetokens
    );

    /**
     * Delete ktpl_devicetokens by ID
     * @param string $ktplDevicetokensId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ktplDevicetokensId);
}

