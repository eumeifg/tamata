<?php


namespace CAT\VIP\Block\Adminhtml\Grid;

/**

* Adminhtml block grid demo records grid block

*

*/

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

	/**

	* @var \Magento\Framework\Module\Manager

	*/

	protected $moduleManager;

	/**

	* @var \Md\Blog\Model\BlogFactory

	*/

	protected $_blogFactory;

	/**

	* @var \Md\Blog\Model\Status

	*/

	protected $_status;

	/**

	* @param \Magento\Backend\Block\Template\Context $context

	* @param \Magento\Backend\Helper\Data            $backendHelper

	* @param \Md\Blog\Model\BlogFactory              $blogFactory

	* @param \Md\Blog\Model\Status                   $status

	* @param \Magento\Framework\Module\Manager       $moduleManager

	* @param array                                   $data

	*/

	public function __construct(

		\Magento\Backend\Block\Template\Context $context,

		\Magento\Backend\Helper\Data $backendHelper,

		\CAT\VIP\Model\VipOrdersFactory $VipOrdersFactory,

		\CAT\VIP\Model\Status $status,

		\Magento\Framework\Module\Manager $moduleManager,

		array $data = []

	) {

		$this->_vipOrdersFactory = $VipOrdersFactory;

		$this->_status = $status;

		$this->moduleManager = $moduleManager;

		parent::__construct($context, $backendHelper, $data);

	}

	/**

	* @return void

	*/

	protected function _construct() {

		parent::_construct();

		$this->setId('gridGrid');

		$this->setDefaultSort('id');

		$this->setDefaultDir('DESC');

		$this->setSaveParametersInSession(true);

		$this->setUseAjax(true);

		$this->setVarNameFilter('grid_record');

	}

	/**

	* @return $this

	*/

	protected function _prepareCollection() {

		$collection = $this->_vipOrdersFactory->create()->getCollection();
						$collection->getSelect('main_table.*,cusname,pname')
						->joinLeft(
						    ['cus' => 'customer_entity'], 
						    'cus.entity_id = main_table.customer_id', 
						     ['cusname' => "CONCAT(firstname,' ',lastname)"]
						    )
						->join(
						    ['prod1' => 'catalog_product_entity'], 
						    'prod1.entity_id = main_table.product_id',
						    ['sku' => "sku"] 
						    );
		$this->setCollection($collection);

		parent::_prepareCollection();

		return $this;

	}

	/**

	* @return $this

	*/

	protected function _prepareColumns() {

		$this->addColumn('entity_id',
				[

					'header' => __('ID'),

					'type' => 'number',

					'index' => 'entity_id',

					'header_css_class' => 'col-id',

					'column_css_class' => 'col-id',

				]
		);

		$this->addColumn(

			'customer_id',

				[

				'header' => __('Customer Name'),

				'index' => 'cusname',

				]

			);

		$this->addColumn(

			'product_id',

				[

				'header' => __('Product SKU'),

				'index' => 'sku',

				]

		);
		$this->addColumn(

			'qty',

				[

				'header' => __('Quantity'),

				'index' => 'qty',

				]

		);


		$this->addColumn(

			'order_id',

				[

				'header' => __('Order ID'),

				'index' => 'order_id',

				]

		);



		$block = $this->getLayout()->getBlock('grid.bottom.links');

		if ($block) {

		$this->setChild('grid.bottom.links', $block);

		}

		return parent::_prepareColumns();

	}



	/**

	* @return string

	*/

	public function getGridUrl() {

	return $this->getUrl('viporder/*/grid', ['_current' => true]);

	}

}