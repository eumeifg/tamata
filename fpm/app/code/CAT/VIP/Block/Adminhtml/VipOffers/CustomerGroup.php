<?php
 
namespace CAT\VIP\Block\Adminhtml\VipOffers;
 
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
 
class CustomerGroup extends AbstractRenderer
{
    protected $_customerGroup;
    protected $options;
 
    public function __construct(
        Context $context,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_customerGroup = $customerGroup; 
        $customerGroups = $this->_customerGroup->toOptionArray();

        $this->options =array(); 

        foreach($customerGroups as $cg){
          $this->options[$cg['value']]=$cg['label'];
        }
    }
 
    public function render(DataObject $row)
    {
        $groups = explode(",",$row->getCustomerGroup());
        $text = '';
        foreach($groups as $key => $group){
            if($key == 0){

                $text = $this->options[$group];
            }
            else{

                $text = $text.', '. $this->options[$group];
            }

        }
        return $text;
    }
}