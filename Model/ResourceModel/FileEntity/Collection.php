<?php 

namespace Naxero\Translation\Model\ResourceModel\FileEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
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
        $this->_init('Naxero\Translation\Model\FileEntity', 'Naxero\Translation\Model\ResourceModel\FileEntity');
    }
}