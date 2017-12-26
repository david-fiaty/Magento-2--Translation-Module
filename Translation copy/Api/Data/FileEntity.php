<?php

namespace Naxero\Translation\Api\Data;


interface FileEntityInterface
{
    /**
     * Constants for keys of data array.
     */
    const FILE_ID       = 'file_id';
    const FILE_PATH     = 'file_path';
    const FILE_CONTENT  = 'file_content';
    const FILE_CREATION_TIME = 'file_creation_time';
    const FILE_UPDATE_TIME   = 'file_update_time';
    const FILE_IS_ACTIVE     = 'file_is_active';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get file path
     *
     * @return string
     */
    public function getFilePath();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getFileContent();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getFileCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getFileUpdateTime();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setId($id);

    /**
     * Set file path
     *
     * @param string $file_path
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setFilePath($filePath);

    /**
     * Set file content
     *
     * @param string $file_content
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setFileContent($fileContent);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setFileCreationTime($fileCreationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setFileUpdateTime($fileUpdateTime);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Naxero\Translation\Api\Data\FileEntityInterface
     */
    public function setIsActive($isActive);
}