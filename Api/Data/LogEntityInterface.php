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

namespace Naxero\Translation\Api\Data;

interface LogEntityInterface
{
    /**
     * Constants for keys of data array.
     */
    const ID = 'id';
    const FILE_ID = 'file_id';
    const ROW_ID = 'row_id';
    const COMMENTS = 'comments';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get file ID
     *
     * @return int|null
     */
    public function getFileId();

    /**
     * Get log row ID
     *
     * @return string
     */
    public function getRowId();

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
     * Set file ID
     *
     * @param int $id
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setFileId($id);

    /**
     * Set log row ID
     *
     * @param string $rowId
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setRowId($rowId);

    /**
     * Set the log comments
     *
     * @param string $comments
     * @return \Naxero\Translation\Api\Data\LogEntityInterface
     */
    public function setComments($comments);
}
