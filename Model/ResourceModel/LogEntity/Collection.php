<?php 

namespace Naxero\Translation\Model\ResourceModel\LogEntity;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            'Naxero\Translation\Model\LogEntity',
            'Naxero\Translation\Model\ResourceModel\LogEntity'
        );
    }
}