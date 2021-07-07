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

namespace Naxero\Translation\Model;

use Naxero\Translation\Api\Data\LogEntityInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class LogEntity extends AbstractModel implements LogEntityInterface, IdentityInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'log_entity';

    /**
     * @var string
     */
    public $_cacheTag = 'log_entity';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    public $_eventPrefix = 'log_entity';

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Naxero\Translation\Model\ResourceModel\LogEntity::class
        );
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get file ID
     *
     * @return int|null
     */
    public function getFileId()
    {
        return $this->getData(self::FILE_ID);
    }

    /**
     * Get log row ID
     *
     * @return string
     */
    public function getRowId()
    {
        return $this->getData(self::ROW_ID);
    }

    /**
     * Get log comments
     *
     * @return string|null
     */
    public function getComments()
    {
        return $this->getData(self::COMMENTS);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set file ID
     *
     * @param int $fileId
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setFileId($fileId)
    {
        return $this->setData(self::FILE_ID, $fileId);
    }

    /**
     * Set row ID
     *
     * @param string $rowId
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setRowId($rowId)
    {
        return $this->setData(self::ROW_ID, $rowId);
    }

    /**
     * Set row comments
     *
     * @param string $comments
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setComments($comments)
    {
        return $this->setData(self::COMMENTS, $comments);
    }
}
