<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Block\Sellerhtml\Order\Creditmemo;

/**
 * @author Rocket Bazaar Core Team
 */

class Listing extends \Magento\Framework\View\Element\Template
{
    const LIMIT = 10;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid\Collection
     */
    protected $creditMemoColln;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Sales\Model\CreditmemoFactory $creditMemoFactory
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Sales\Model\CreditmemoFactory $creditMemoFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magedelight\Backend\Model\Auth\Session $authSession
    ) {
        $this->creditMemoColln = $creditMemoFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setCollection($this->getCreditMemoCollection());
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!empty($this->getCollection())) {

            /** @var \Magento\Theme\Block\Html\Pager */
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.creditmemo.list.pager'
            );
            $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
            $pager->setLimit(self::LIMIT)
                ->setCollection($this->getCollection());
            $this->setChild('pager', $pager);

            $this->getCollection()->load();
            return $this;
        }
    }

    /**
     *
     * @return array
     */
    public function getCreditMemoCollection()
    {
        $collection = $this->creditMemoColln->create()->getCollection();
        $collection->getSelect()->join(
            ['sales_creditmemo'],
            'main_table.entity_id = sales_creditmemo.entity_id AND vendor_id = '
            . $this->authSession->getUser()->getVendorId() . '',
            ['vendor_id']
        );

        $collection->setOrder('created_at', 'DESC');
        return $collection;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getCreditmemoState($stateId = null)
    {
        if ($stateId) {
            $options = [];
            /** @var \Magento\Framework\Phrase $state */
            foreach ($this->creditmemoRepository->create()->getStates() as $id => $state) {
                if ($id  == $stateId) {
                    return $state->render();
                }
            }
        }
    }
}
