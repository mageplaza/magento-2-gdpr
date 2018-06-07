<?php

namespace Mageplaza\Gdpr\Block;

use Mageplaza\Gdpr\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;
/**
 * Block Extradata
 */
class Extradata extends \Magento\Framework\View\Element\Template
{
    /**
     * get request http
     * \Magento\Framework\App\Request\Http $request
     */
    protected $_request;

    /**
     * store manager.
     *
     * @var
     */
    protected$_storeManager;

    /**
     * @var \Mageplaza\Gdpr\Helper\Data
     */
    protected $_helper;

    /**
     * Block constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        StoreManagerInterface $storeManager,
        Http $request,
        Data $heper,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_storeManager=$storeManager;
        $this->_request = $request;
        $this->_helper  = $heper;
    }

    /**
     * @return string
     */
    public function getbaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getRouterName()
    {
        return $this->_request->getRouteName();
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->_request->getModuleName();
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->_request->getControllerName();
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->_request->getActionName();
    }

    /**
     * get Current URL
     */
    public function getCurrentUrl()
    {
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
    }

    /**
     * get Real URL
     */
    public function getUrlReal()
    {
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => false]);
    }

    /**
     * get Extra Data Content
     * @return array
     */
    public function getExtraDataContent()
    {
        $extraData = array(
            'lazyload' => $this->getViewFileUrl('images/loader-1.gif'),
            'currentControllerAction' => 'account-edit',
            'checkpasswordUrl'  => $this->getUrl('customer/account/checkpassword')
        );
        return Data::jsonEncode($extraData);
    }

}
