<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Controller\Adminhtml\Scan;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var DirectoryList
     */
    protected $tree;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var View
     */
    protected $viewHelper;

    /**
     * @var FileEntityFactory
     */
    protected $fileEntityFactory;

    /**
     * @var LogDataService
     */
    protected $logDataService;

    /**
     * Index class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem\DirectoryList $tree,
        \Naxero\Translation\Helper\Data $helper,
        \Naxero\Translation\Helper\View $viewHelper,
        \Naxero\Translation\Model\FileEntityFactory $fileEntityFactory,
        \Naxero\Translation\Model\Service\LogDataService $logDataService
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tree = $tree;
        $this->helper = $helper;
        $this->viewHelper = $viewHelper;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->logDataService = $logDataService;
        
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        // Prepare the output
        $result = $this->resultJsonFactory->create();

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
            foreach(new \RecursiveIteratorIterator($rdi) as $filePath)
            {
                if ($this->isWantedFile($filePath)) {
                    $this->saveFile($filePath);
                }
            }

            // Get the output
            $output = $this->viewHelper->render($view);
        }

        return $result->setData($output);
    }

    public function clearTableData() {
        $fileEntity = $this->fileEntityFactory->create(); 
        $connection = $fileEntity->getCollection()->getConnection();
        $tableName  = $fileEntity->getCollection()->getMainTable();
        $connection->truncateTable($tableName);
    }

    public function saveFile($filePath)
    {
        // Get the clean path
        $cleanPath = $this->helper->getCleanPath($filePath);

        // Save the item
        $fileEntity = $this->fileEntityFactory->create(); 
        $fileEntity->setData('file_path', $cleanPath);
        $fileEntity->setData('file_content', file_get_contents($filePath));
        $fileEntity->setData('file_is_active', 1);
        $fileEntity->setData('file_creation_time', date("Y-m-d H:i:s"));
        $fileEntity->setData('file_update_time', date("Y-m-d H:i:s"));
        $fileEntity->save();

        // Get the entity data
        $arr = $fileEntity->getData();

        // Get the content rows
        $rows = explode(PHP_EOL, $arr['file_content']);

        // Loop through the rows
        $rowId = 0;
        foreach ($rows as $row) {        
            // Get the line
            $line = str_getcsv($row);

            // Check errors
            $this->logDataService->hasErrors($line, $arr['file_id'], $rowId);

            // Increment
            $rowId++;
        }
    }

    public function isWantedFile($filePath)
    {
        return (pathinfo($filePath, PATHINFO_EXTENSION) == 'csv')
        && (is_file($filePath))
        && (strpos($filePath, 'i18n') !== false)
        && !$this->isIndexed($filePath);          
    }

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
