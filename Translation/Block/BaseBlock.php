<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Block;
use Magento\Framework\UrlFactory;
class BaseBlock extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Naxero\Translation\Helper\Data
     */
	 protected $devToolHelper;
	 
	 /**
     * @var \Magento\Framework\Url
     */
	 protected $urlApp;
	 
	 /**
     * @var \Naxero\Translation\Model\Config
     */
    protected $config;

    /**
     * @param \Naxero\Translation\Block\Context $context
	 * @param \Magento\Framework\UrlFactory $urlFactory
     */
    public function __construct(
		\Naxero\Translation\Block\Context $context
	)
    {
        $this->devToolHelper = $context->getTranslationHelper();
		$this->config = $context->getConfig();
        $this->urlApp = $context->getUrlFactory()->create();
		parent::__construct($context);
    }
	
	/**
	 * Function for getting event details
	 * @return array
	 */
    public function getEventDetails()
    {
		return  $this->devToolHelper->getEventDetails();
    }
	
	/**
     * Function for getting current url
	 * @return string
     */
	public function getCurrentUrl(){
		return $this->urlApp->getCurrentUrl();
	}
	
	/**
     * Function for getting controller url for given router path
	 * @param string $routePath
	 * @return string
     */
	public function getControllerUrl($routePath){
		
		return $this->urlApp->getUrl($routePath);
	}
	
	/**
     * Function for getting current url
	 * @param string $path
	 * @return string
     */
	public function getConfigValue($path){
		return $this->config->getCurrentStoreConfigValue($path);
	}
	
	/**
     * Function canShowTranslation
	 * @return bool
     */
	public function canShowTranslation(){
		$isEnabled = $this->getConfigValue('translation/module/is_enabled');
		if ($isEnabled)
		{
			$allowedIps=$this->getConfigValue('translation/module/allowed_ip');
			 if(is_null($allowedIps)){
				return true;
			}
			else {
				$remoteIp = $_SERVER['REMOTE_ADDR'];
				if (strpos($allowedIps,$remoteIp) !== false) {
					return true;
				}
			}
		}
		return false;
	}
	
}
