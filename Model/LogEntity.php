<?php 

namespace Naxero\Translation\Model;

class LogEntity extends \Magento\Framework\Model\AbstractModel 
implements \Naxero\Translation\Api\Data\LogEntityInterface, \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'log_entity';

    /**
     * @var string
     */
    protected $_cacheTag = 'log_entity';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'log_entity';

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Naxero\Translation\Model\ResourceModel\LogEntity');
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
        return $this->getData(self::FILE_ID);
    }

    /**
     * Get log row
     *
     * @return string
     */
    public function getRow()
    {
        return $this->getData(self::FILE_ROW);
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
        return $this->setData(self::FILE_ID, $id);
    }

    /**
     * Set row id
     *
     * @param string $rowId
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setRow($rowId)
    {
        return $this->setData(self::FILE_ROW, $rowId);
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