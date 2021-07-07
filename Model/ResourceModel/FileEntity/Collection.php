<?php
/**
 * Naxero.com
 * Professional ecommerce integrations for Magento
 *
 * PHP version 7
 *
 * @category  Magento2
 * @package   Naxero
 * @author    Platforms Development Team <contact@naxero.com>
 * @copyright Naxero.com
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.naxero.com
 */

namespace Naxero\Translation\Model\ResourceModel\FileEntity;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    public $_idFieldName = 'file_id';

    /**
     * Define the resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Naxero\Translation\Model\FileEntity::class,
            \Naxero\Translation\Model\ResourceModel\FileEntity::class
        );
    }
}
