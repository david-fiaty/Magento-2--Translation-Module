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
     * @var Filesystem
     */
    public $fileSystem;
 
    /**
     * @var UploaderFactory
     */
    public $uploaderFactory;

    /**
     * @var Reader
     */
    public $moduleReader;

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
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Naxero\Translation\Model\Service\LogDataService $logDataService
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->tree = $tree;
        $this->csvParser = $csvParser;
        $this->fileDriver = $fileDriver;
        $this->helper = $helper;
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->moduleReader = $moduleReader;
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

        // Process the request
        if ($this->getRequest()->isAjax()) 
        {
            // Get the request parameters
            $action  = $this->getRequest()->getParam('action');
            $isLogView = $this->getRequest()->getParam('is_log_view');

            // Get data
            switch ($action) {
                case 'get_data':
                    $output = $this->getFileEntityContent($isLogView);
                    break;
    
                case 'update_data':
                    $output = $this->updateFileEntityContent();
                    break;

                case 'save_data':
                    $output = $this->saveFileEntityContent();
                    break;

                case 'delete_row':
                    $output = $this->deleteFileEntityRow();
                    break;

                case 'delete_file':
                    $output = $this->deleteCsvFile();
                    break;

                case 'import_data':
                    $output = $this->importFileData();
                    break;
            }
        }

        // Return the content
        return $this->resultJsonFactory->create()->setData($output);
    }

    /**
     * Get a file instance.
     */
    public function getFileInstance() {
        // Get the file id
        $fileId = $this->getRequest()->getParam('file_id');

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/0.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($fileId, 1));

        // Load the requested item
        if ((int) $fileId > 0) {
            return $this->fileEntityFactory
                ->create()
                ->load($fileId);
        }   

        return null;
    }

    /**
     * Import file data.
     */
    public function importFileData() {
        // Prepare the output array
        $output = [
            'success' => true,
            'message' => __('The file has been imported successfully.')
        ];

        try {
            // Set the file destination
            $destinationPath = $this->moduleReader->getModuleDir(
                '',
                'Naxero_Translation'
            ) . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Upload';

            // Get the uploader
            $uploader = $this->uploaderFactory->create(['fileId' => 'new_file_import'])
                ->setAllowCreateFolders(true)
                ->setAllowedExtensions(['csv']);

            // Save the uploaded file
            $fileName = uniqid() . '.csv';
            $uploader->save($destinationPath, $fileName);

            // Get the file entity instance
            $fileEntity = $this->getFileInstance();

            // Get the current content
            $content = $fileEntity->getFileContent();

            // Convert the content to array
            $lines = json_decode($content);

            // Get the uploaded content
            $uploadedFilePath = $destinationPath . DIRECTORY_SEPARATOR . $fileName;
            $uploadedLines = $this->csvParser->getData($uploadedFilePath);

            // Merge and encode the new content
            $newLines = array_merge($lines, $uploadedLines);
            $newContent = json_encode($newLines);

            // Save the new content to db
            $fileEntity->setFileContent($newContent);
            $fileEntity->setRowsCount(count($newLines));
            $fileEntity->save();

            // Update the CSV file
            $this->saveFileEntityContent($fileEntity);

            // Delete the uploaded file
            $this->fileDriver->deleteFile($uploadedFilePath);

        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'message' => __('There was an error importing the file.')
            ];
        }

        return $output;
    }

    /**
     * Delete a CSV file.
     */
    public function deleteCsvFile() {
        // Prepare the output array
        $output = [
            'success' => false,
            'message' => __('There was an error deleting the file.')
        ];

        try {
            // Get the root path
            $rootPath = $this->tree->getRoot();

            // Get the file entity instance
            $fileEntity = $this->getFileInstance();
            if ($fileEntity) {
                // Prepare the full file path
                $filePath = $rootPath . DIRECTORY_SEPARATOR
                . $fileEntity->getData('file_path');

                // Delete the file on the server
                $this->fileDriver->deleteFile($filePath);

                // Delete the file entity in database
                $fileEntity->delete();

                $output = [
                    'success' => true,
                    'message' => __('The file has been deleted successfully.')
                ];
            }
        }
        catch (\Exception $e) {
            $output = [
                'success' => false,
                'message' => __($e->getMessage())
            ];
        }

        return $output;
    }

    /**
     * Delete a file entity row in database.
     */
    public function deleteFileEntityRow() {
        try {
            // Get the file entity instance
            $fileEntity = $this->getFileInstance();

            // Prepare the row data
            $rowId = $this->getRequest()->getParam('row_id');

            // Get the current content
            $content = $fileEntity->getFileContent();

            // Convert the content to array
            $lines = json_decode($content);

            // Delete the row
            unset($lines[$rowId]);

            // Reset the indexes
            $lines = array_values($lines);

            // Encode the new content
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
            return false;
        }
    }

    /**
     * Update a file entity content in database.
     */
    public function updateFileEntityContent() {
        // Get the file entity instance
        $fileEntity = $this->getFileInstance();

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

            // Get the row id from index
            $rowId = $params['row_content']['row_id'];

            // Update the row
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
            return false;
        }
    }

    /**
     * Save a file content in the file system.
     */
    public function saveFileEntityContent() {
        // Get the file entity instance
        $fileEntity = $this->getFileInstance();

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
            return false;
        }
    }

    /**
     * Get a file content from database.
     */
    public function getFileEntityContent($isLogView) {
        // Get the file entity instance
        $fileEntity = $this->getFileInstance();

        // Prepare the output array
        $output = [
            'table_data' => [],
            'error_data' => []
        ]; 

        // Get the file content rows
        $rows = json_decode($fileEntity->getData('file_content'));

        // Get the file id
        $fileId = $fileEntity->getData('file_id');

        // Loop through the rows
        if (!empty($rows)) {
            $rowId = 0;
            foreach ($rows as $row) {
                $rowIndex = $rowId + 1;
                if (!$this->logDataService->hasErrors($fileId, $row, $rowId)) {
                    $output['table_data'][] = $this->buildRow($row, $rowId, $rowIndex, $fileEntity);
                }
                else {
                    $output['table_data'][] = $this->buildErrorRow($row, $rowId, $rowIndex, $fileEntity);
                    $output['error_data'][] = $rowIndex;
                }
                $rowId++;
            }
        }

        return $output;
    }

    /**
     * Prepare a file row content for display.
     */
    public function buildRow($rowDataArray, $rowId, $rowIndex, $fileEntity) {
        // Add the index to the row array
        array_unshift($rowDataArray, $rowIndex);

        // Add the file id and row id
        $rowDataArray['row_id'] = $rowId;
        $rowDataArray['file_id'] = $fileEntity->getData('file_id');

        // Add the read/write state
        $rowDataArray['is_readable'] = $fileEntity->getData('is_readable');
        $rowDataArray['is_writable'] = $fileEntity->getData('is_writable');

        // Add the error state
        $rowDataArray['is_error'] = 0;

        // Retun combined data
        return (object) array_combine(
            $this->getColumns(),
            $rowDataArray
        );
    }

    /**
     * Prepare a file content row error for display.
     */
    public function buildErrorRow($rowDataArray, $rowId, $rowIndex, $fileEntity) {
        // Build the error line
        $errorLine = [];
        $errorLine[] = $rowIndex;
        $errorLine[] = isset($rowDataArray[0]) ? $rowDataArray[0] : '';
        $errorLine[] = isset($rowDataArray[1]) ? $rowDataArray[1] : '';

        // Add the file id and row id
        $errorLine['row_id'] = $rowId;
        $errorLine['file_id'] = $fileEntity->getData('file_id');

        // Add the read/write state
        $errorLine['is_readable'] = $fileEntity->getData('is_readable');
        $errorLine['is_writable'] = $fileEntity->getData('is_writable');

        // Add the error state
        $errorLine['is_error'] = 1;

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
            'index',
            'key',
            'value',
            'row_id',
            'file_id',
            'is_readable',
            'is_writable',
            'is_error'
        ];
    }
}
