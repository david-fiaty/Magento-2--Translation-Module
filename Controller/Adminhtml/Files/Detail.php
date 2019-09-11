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

namespace Naxero\Translation\Controller\Adminhtml\Files;

use Magento\Framework\Exception\LocalizedException;

class Detail extends \Magento\Backend\App\Action
{
	/**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var FileEntityFactory
     */
    public $fileEntityFactory;    

    /**
     * @var Csv
     */
    public $csvParser;

    /**
     * @var File
     */
    public $fileDriver;

    /**
     * @var Data
     */
	public $helper;

    /**
     * @var DirectoryList
     */
    public $tree;

    /**
     * @var LogDataService
     */
    public $logDataService;

    /**
     * Detail class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Naxero\Translation\Model\FileEntityFactory $fileEntityFactory,
        \Magento\Framework\Filesystem\DirectoryList $tree,
        \Magento\Framework\File\Csv $csvParser,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Naxero\Translation\Helper\Data $helper,
        \Naxero\Translation\Model\Service\LogDataService $logDataService
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->tree = $tree;
        $this->csvParser = $csvParser;
        $this->fileDriver = $fileDriver;
        $this->helper = $helper;
        $this->logDataService = $logDataService;

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        // Prepare the response instance
        $output = [];
        $result = $this->resultJsonFactory->create();

        // Process the request
        if ($this->getRequest()->isAjax()) 
        {
            // Get the request parameters
            $action  = $this->getRequest()->getParam('action');
            $fileId = $this->getRequest()->getParam('file_id');
            $isLogView = $this->getRequest()->getParam('is_log_view');

            // Load the requested item
            $fileInstance = $this->fileEntityFactory
                ->create()
                ->load($fileId);

            // Get data
            if ($fileInstance->getId() > 0) {
                switch ($action) {
                    case 'get_data':
                        $output = $this->getFileEntityContent($fileInstance, $isLogView);
                        break;
        
                    case 'update_data':
                        $output = $this->updateFileEntityContent($fileInstance);
                        break;

                    case 'save_data':
                        $output = $this->saveFileEntityContent($fileInstance);
                        break;
                }
            }
        }

        // Return the content
        return $result->setData($output);
    }

    /**
     * Update a file entity content in database.
     */
    public function updateFileEntityContent($fileEntity) {
        // Prepare the new content
        $params = $this->getRequest()->getParams();
        $newRrow = [
            $params['row_content']['key'],
            $params['row_content']['value']
        ];

        // Insert the new data
        try {
            // Get the current content
            $content = $fileEntity->getFileContent();

            // Convert the content to array
            $lines = json_decode($content);

            // Get the row id
            $rowId = $params['row_content']['row_id'];

            $lines[$rowId] = $newRrow;
            $newContent = json_encode($lines);

            // Save the new content to db
            $fileEntity->setFileContent($newContent);
            $fileEntity->setRowsCount(count($lines));
            $fileEntity->save();

            // Update the CSV file
            $this->saveFileEntityContent($fileEntity);

            return true;
        }
        catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return false;
    }

    /**
     * Save a file content in the file system.
     */
    public function saveFileEntityContent($fileEntity) {
        // Get the root path
        $rootPath = $this->tree->getRoot();

        // Save the data
        try {
            // Prepare the full file path
            $filePath = $rootPath . DIRECTORY_SEPARATOR . $fileEntity->getData('file_path');

            // Save the file
            return $this->csvParser->saveData(
                $filePath,
                json_decode($fileEntity->getData('file_content'))
            );
        }
        catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return false;
    }

    /**
     * Get a file content from database.
     */
    public function getFileEntityContent($fileEntity, $isLogView) {
        // Prepare the output array
        $output = array(); 

        // Get the file content rows
        $rows = json_decode($fileEntity->getData('file_content'));

        // Get the file id
        $fileId = $fileEntity->getData('file_id');

        // Loop through the rows
        $rowId = 0;
        foreach ($rows as $row) {
            if (!$this->logDataService->hasErrors($fileId, $row, $rowId)) {
                $output['table_data'][] = $this->buildRow($row, $rowId, $fileEntity);
            }
            else {
                $output['table_data'][] = $this->buildErrorRow($row, $rowId, $fileEntity);
                $output['error_data'][] = $rowId;
            }
            $rowId++;
        }

        return $output;
    }

    /**
     * Prepare a file row content for display.
     */
    public function buildRow($rowDataArray, $rowId, $fileEntity) {
        // Add the file and row id
        $rowDataArray['file_id'] = $fileEntity->getData('file_id');
        $rowDataArray['row_id'] = $rowId;

        // Add the read/write state
        $rowDataArray['is_readable'] = $fileEntity->getData('is_readable');
        $rowDataArray['is_writable'] = $fileEntity->getData('is_writable');

        // Retun combined data
        return (object) array_combine(
            $this->getColumns(),
            $rowDataArray
        );
    }

    /**
     * Prepare a file content row error for display.
     */
    public function buildErrorRow($rowDataArray, $rowId, $fileEntity) {
        // Build the error line
        $errorLine = [];
        $errorLine[] = isset($rowDataArray[0]) ? $rowDataArray[0] : '';
        $errorLine[] = isset($rowDataArray[1]) ? $rowDataArray[1] : '';

        // Add the file and row id
        $rowDataArray['file_id'] = $fileEntity->getData('file_id');
        $rowDataArray['row_id'] = $rowId;

        // Add the read/write state
        $errorLine['is_readable'] = $fileEntity->getData('is_readable');
        $errorLine['is_writable'] = $fileEntity->getData('is_writable');

        // Retun combined data
        return (object) array_combine(
            $this->getColumns(),
            $errorLine
        );
    }

    /**
     * Get the detail table columns.
     */
    public function getColumns() {
        return [
            'key',
            'value',
            'file_id',
            'row_id',
            'is_readable',
            'is_writable'
        ];
    }
}
