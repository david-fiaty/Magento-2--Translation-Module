<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Helper;


class View extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var FileDataService
     */
    public $fileDataService; 

    /**
     * @var StringDataService
     */
    public $stringDataService;

    /**
     * @var LogDataService
     */
    public $logDataService;

	/**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
        \Naxero\Translation\Model\Service\FileDataService $fileDataService,
        \Naxero\Translation\Model\Service\StringDataService $stringDataService,
        \Naxero\Translation\Model\Service\LogDataService $logDataService
	) {
		parent::__construct($context);
        $this->fileDataService = $fileDataService;
        $this->stringDataService = $stringDataService;
        $this->logDataService = $logDataService;
	}

    /**
     * Provide data for a JS table.
     */
    public function render($view) {
        // Prepare the output
        $output = [];

        // Process the view case
        switch ($view) {
            case 'files':
                $output = $this->fileDataService->init()->getList();
                break;

            case 'strings':
                $output = $this->stringDataService->init()->getList();
                break;

            case 'logs':
                $output = $this->logDataService->init()->getList();
                break;
        }

        return $output;
    }
}