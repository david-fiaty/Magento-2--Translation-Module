<?php

namespace Naxero\Translation\Api\Data;


interface LogEntityInterface
{
    /**
     * Constants for keys of data array.
     */
    const FILE_ID       = 'file_id';
    const FILE_ROW     = 'file_row';
    const COMMENTS  = 'comments';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get log row
     *
     * @return string
     */
    public function getRow();

    /**
     * Get comments
     *
     * @return string|null
     */
    public function getComments();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setId($id);

    /**
     * Set log row
     *
     * @param string $rowId
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setRow($rowId);

    /**
     * Set the row comments
     *
     * @param string $comments
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setComments($comments);
}