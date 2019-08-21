<?php
namespace Naxero\Translation\Model\ResourceModel;

/**
 * File entity mysql resource
 */
class FileEntity extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('naxero_translation_file', 'file_id');
    }

    /**
     * Process file data before saving
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
            $object->setCreationTime($this->_date->gmtDate());
        }

        $object->setUpdateTime($this->_date->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Load an object using 'file_path' field if there's no field specified
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
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Naxero\Translation\Model\FileEntity $object
     * @return \Zend_Db_Select
     */
    public function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {

            $select->where(
                'file_is_active = ?',
                1
            )->limit(
                1
            );
        }

        return $select;
    }

    /**
     * Retrieve load select with filter by file path and activity
     *
     * @param string $filePath
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     */
    public function _getLoadByFilePathSelect($filePath, $isActive = null)
    {
        $select = $this->getConnection()->select()->from(
            ['bp' => $this->getMainTable()]
        )->where('bp.file_path = ?', $filePath);

        if (!is_null($isActive)) {
            $select->where('bp.file_is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     *  Check whether file ath key is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function isNumericFilePath(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('file_path'));
    }

    /**
     *  Check whether file path is valid
     *  Should be a regular expression checking only the string
     * 
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function isValidFilePath(\Magento\Framework\Model\AbstractModel $object)
    {
        //return is_file($object->getData('file_path'));
        return true;
    }

    /**
     * Check if file path exists in db
     * return file id if file path exists
     *
     * @param string $filePath
     * @return int
     */
    public function checkFilePath($filePath)
    {
        $select = $this->_getLoadByFilePathSelect($filePath, 1);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('bp.filet_id')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }
}