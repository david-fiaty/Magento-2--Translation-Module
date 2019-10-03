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

class Create extends \Magento\Backend\App\Action
{
	/**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var Data
     */
    public $helper;
    
    /**
     * @var FileDataService
     */
    public $fileDataService;

    /**
     * Create class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Naxero\Translation\Helper\Data $helper,
        \Naxero\Translation\Model\Service\FileDataService $fileDataService
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->fileDataService = $fileDataService;
    }

    /**
     * Create action
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        // Prepare the output array
        $output = [
            'success' => false,
            'message' => __('There was an error creating the file.')
        ];

        // Process the request
        if ($this->getRequest()->isAjax()) 
        {
            // Build the new file path
            $newFilePath = $this->getNewFilePath();

            // Handle the file creation
            if ($newFilePath) {
                try {
                    // Create the file
                    $result1 = $this->helper->createFile($newFilePath);

                    // Get the clean path
                    $cleanPath = $this->helper->getCleanPath($newFilePath);

                    // Save the file entity
                    $result2 = $this->fileDataService->saveFileEntity([
                        'is_readable' => true,
                        'is_writable' => true,
                        'file_path' => $cleanPath,
                        'file_content' => json_encode([]),
                        'rows_count' => 0,
                        'file_creation_time' => date("Y-m-d H:i:s"),
                        'file_update_time' => date("Y-m-d H:i:s")
                    ]);

                    // Build the response message
                    if ($result1 || $result2) {
                        $output = [
                            'success' => true,
                            'message' => __('The file has been created successfully.')
                        ];
                    }
                }
                catch(\Exception $e) {
                    $output = [
                        'success' => false,
                        'message' => __($e->getMessage())
                    ];
                }
            }
        }

        // Return the response
        return $this->resultJsonFactory->create()->setData($output);
    }

    /**
     * Build the new file path.
     */
    public function getNewFilePath()
    {
        // Get the request parameters
        $filePath  = $this->getRequest()->getParam('file_path');
        $fileName = $this->getRequest()->getParam('file_name');

        // Get the file extension
        $fileExtension = explode('.', $fileName);

        // Build the path
        if (is_dir($filePath) && $fileExtension[1] == 'csv') {
            return $filePath . $fileName;
        }

        return null;
    }
}
