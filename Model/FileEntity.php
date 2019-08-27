<?php 

namespace Naxero\Translation\Model;

class FileEntity extends \Magento\Framework\Model\AbstractModel 
implements \Naxero\Translation\Api\Data\FileEntityInterface, \Magento\Framework\DataObject\IdentityInterface
{
    /**#@+
     * Files's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'file_entity';

    /**
     * @var string
     */
    protected $_cacheTag = 'file_entity';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'file_entity';

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Naxero\Translation\Model\ResourceModel\FileEntity');
    }

    /**
     * Check if file path exists
     * return file id if file path exists
     *
     * @param string $file_path
     * @return int
     */
    public function checkFilePath($file_path)
    {
        return $this->_getResource()->checkFilePath($file_path);
    }

    /**
     * Prepare the file's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
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
     * Get file path 
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->getData(self::FILE_PATH);
    }

    /**
     * Get file content
     *
     * @return string|null
     */
    public function getFileContent()
    {
        return $this->getData(self::FILE_CONTENT);
    }

    /**
     * Get file creation time
     *
     * @return string|null
     */
    public function getFileCreationTime()
    {
        return $this->getData(self::FILE_CREATION_TIME);
    }

    /**
     * Get file update time
     *
     * @return string|null
     */
    public function getFileUpdateTime()
    {
        return $this->getData(self::FILE_UPDATE_TIME);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setId($id)
    {
        return $this->setData(self::FILE_ID, $id);
    }

    /**
     * Set file path
     *
     * @param string $file_path
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setFilePath($filePath)
    {
        return $this->setData(self::FILE_PATH, $filePath);
    }

    /**
     * Set file content
     *
     * @param string $content
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setFileContent($fileContent)
    {
        return $this->setData(self::FILE_CONTENT, $fileContent);
    }

    /**
     * Set file creation time
     *
     * @param string $fileCreationTime
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setFileCreationTime($fileCreationTime)
    {
        return $this->setData(self::FILE_CREATION_TIME, $fileCreationTime);
    }

    /**
     * Set update time
     *
     * @param string $fileUpdateTime
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setFileUpdateTime($fileUpdateTime)
    {
        return $this->setData(self::FILE_UPDATE_TIME, $fileUpdateTime);
    }
}