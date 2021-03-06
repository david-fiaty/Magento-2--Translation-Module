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

namespace Naxero\Translation\Model\ResourceModel;

/**
 * File entity mysql resource
 */
class FileEntity extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateStamp;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateStamp
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateStamp,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->dateStamp = $dateStamp;
    }

    /**
     * Initialize resource model.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('naxero_translation_files', 'file_id');
    }

    /**
     * Process file data before saving.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$this->isValidFilePath($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The file path is invalid.')
            );
        }

        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreationTime($this->dateStamp->gmtDate());
        }

        $object->setUpdateTime($this->dateStamp->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Load an object using 'file_path' field if there's no field specified.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'file_path';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Retrieve select object for load object data.
     *
     * @param string $field
     * @param mixed $value
     * @param \Naxero\Translation\Model\FileEntity $object
     * @return \Zend_Db_Select
     */
    public function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        return $select;
    }

    /**
     * Retrieve load select with filter by file path and activity.
     *
     * @param string $filePath
     * @return \Magento\Framework\DB\Select
     */
    public function _getLoadByFilePathSelect($filePath)
    {
        $select = $this->getConnection()->select()->from(
            ['bp' => $this->getMainTable()]
        )->where('bp.file_path = ?', $filePath);

        return $select;
    }

    /**
     *  Check whether a file path is numeric.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function isNumericFilePath(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('file_path'));
    }

    /**
     *  Check whether file path is valid.
     *  Should be a regular expression checking only the string.
     * 
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function isValidFilePath(\Magento\Framework\Model\AbstractModel $object)
    {
        return is_file($object->getData('file_path'));
    }

    /**
     * Check if file path exists in db.
     * return file id if file path exists.
     *
     * @param string $filePath
     * @return int
     */
    public function checkFilePath($filePath)
    {
        $select = $this->_getLoadByFilePathSelect($filePath);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('bp.file_id')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }
}