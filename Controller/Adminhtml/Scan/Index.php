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

namespace Naxero\Translation\Controller\Adminhtml\Scan;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var DirectoryList
     */
    public $tree;

    /**
     * @var File
     */
    public $fileDriver;

    /**
     * @var Csv
     */
    public $csvParser;
    
    /**
     * @var Data
     */
    public $helper;

    /**
     * @var View
     */
    public $viewHelper;

    /**
     * @var FileEntityFactory
     */
    public $fileEntityFactory;

    /**
     * @var LogEntityFactory
     */
    public $logEntityFactory;

    /**
     * @var LogDataService
     */
    public $logDataService;

    /**
     * @var FileDataService
     */
    public $fileDataService;

    /**
     * Index class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem\DirectoryList $tree,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\File\Csv $csvParser,
        \Naxero\Translation\Helper\Data $helper,
        \Naxero\Translation\Helper\View $viewHelper,
        \Naxero\Translation\Model\FileEntityFactory $fileEntityFactory,
        \Naxero\Translation\Model\LogEntityFactory $logEntityFactory,
        \Naxero\Translation\Model\Service\LogDataService $logDataService,
        \Naxero\Translation\Model\Service\FileDataService $fileDataService
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tree = $tree;
        $this->fileDriver = $fileDriver;
        $this->csvParser = $csvParser;
        $this->helper = $helper;
        $this->viewHelper = $viewHelper;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->logEntityFactory = $logEntityFactory;
        $this->logDataService = $logDataService;
        $this->fileDataService = $fileDataService;
        
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        // Prepare the output
        $output = [];

        // Loop through the directory tree
        if ($this->getRequest()->isAjax()) 
        {
            // Get the update mode
            $update_mode = $this->getRequest()->getParam('update_mode');

            // Get the view mode
            $view = $this->getRequest()->getParam('view');

            // Clear the table data
            if ($update_mode == 'update_replace') {
                $this->clearTableData();
            }

            // Get the root directory
            $rootPath = $this->tree->getRoot();

            // Scan the files
            $rdi = new \RecursiveDirectoryIterator($rootPath);
            foreach (new \RecursiveIteratorIterator($rdi) as $filePath)
            {
                if ($this->isWantedFile($filePath)) {
                    $this->saveFile($filePath);
                }
            }

            // Get the output
            $output = $this->viewHelper->render($view);
        }

        return $this->resultJsonFactory->create()->setData($output);
    }

    /**
     * Clear the file records in database.
     */
    public function clearTableData() {
        // Clear the files index
        $fileEntity = $this->fileEntityFactory->create(); 
        $connection = $fileEntity->getCollection()->getConnection();
        $tableName  = $fileEntity->getCollection()->getMainTable();
        $connection->truncateTable($tableName);

        // Clear the logs index
        $logEntity = $this->logEntityFactory->create(); 
        $connection = $logEntity->getCollection()->getConnection();
        $tableName  = $logEntity->getCollection()->getMainTable();
        $connection->truncateTable($tableName);
    }

    /**
     * Save a file record in database.
     */
    public function saveFile($filePath)
    {
        // Initial file state
        $fileContent = '';
        $rowsCount = 0;
        $isReadable = $this->helper->isReadable($filePath);
        $isWritable = $this->helper->isWritable($filePath);

        // Get the clean path
        $cleanPath = $this->helper->getCleanPath($filePath);

        // Get the file content
        if ($isReadable) {
            $fileContentArray = $this->csvParser->getData($filePath);
            $fileContent = json_encode($fileContentArray);
            $rowsCount = count($fileContentArray);
        }

        // Save the item
        $fileEntity = $this->fileDataService->saveFileEntity([
            'is_readable' => $isReadable,
            'is_writable' => $isWritable,
            'file_path' => $cleanPath,
            'file_content' => $fileContent,
            'rows_count' => $rowsCount,
            'file_creation_time' => date("Y-m-d H:i:s"),
            'file_update_time' => date("Y-m-d H:i:s")
        ]);

        // Get the entity data
        $arr = $fileEntity->getData();

        // If the file is readable
        if ($isReadable) {
            // Get the content rows
            $rows = json_decode($arr['file_content']);

            // Loop through the rows
            $rowId = 0;
            foreach ($rows as $row) {        
                // Check errors
                $this->logDataService->hasErrors($arr['file_id'], $row, $rowId);

                // Increment
                $rowId++;
            }
        }
        else {
            // Create the log error
            $this->logDataService->createLog(
                4,
                $arr['file_id'],
                $rowId = null
            );
        }

        // Check the file is writable
        if (!$isWritable) {
            $this->logDataService->createLog(
                5,
                $arr['file_id'],
                $rowId = null
            );        
        }
    }

    /**
     * Check if a file is valid for indexing in database.
     */
    public function isWantedFile($filePath)
    {
        return pathinfo($filePath, PATHINFO_EXTENSION) == 'csv'
        && is_file($filePath)
        && strpos($filePath, 'i18n') !== false
        && !$this->isIndexed($filePath);          
    }

    /**
     * Check if a file is already indexed in database.
     */
    public function isIndexed($filePath) {
        // Get the update mode
        $update_mode = $this->getRequest()->getParam('update_mode');

        if ($update_mode == 'update_add') {
            // Get the clean path
            $cleanPath = $this->helper->getCleanPath($filePath);

            // Create the collection
            $fileEntity = $this->fileEntityFactory->create(); 
            $collection = $fileEntity->getCollection();

            // Prepare the output array
            foreach($collection as $item)
            {
                if ($fileEntity->getData('file_path') == $cleanPath) {
                    return true;
                }
            }    
        }

        return false;    
    }
}
