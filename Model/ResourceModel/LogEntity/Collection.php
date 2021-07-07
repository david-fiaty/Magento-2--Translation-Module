<?php

namespace Naxero\Translation\Model\ResourceModel\LogEntity;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    public $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Naxero\Translation\Model\LogEntity::class,
            \Naxero\Translation\Model\ResourceModel\LogEntity::class
        );
    }
}
