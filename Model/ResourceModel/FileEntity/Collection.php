<?php 

namespace Naxero\Translation\Model\ResourceModel\FileEntity;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'file_id';

    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            'Naxero\Translation\Model\FileEntity',
            'Naxero\Translation\Model\ResourceModel\FileEntity'
        );
    }
}