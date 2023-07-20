<?php

namespace NetsEasyPay\Helper;

use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;



class SessionHelper
{
    /** @var FrontendSessionStorageFactoryContract $sessionStorage */
    private $sessionStorage;
    
    /**
     * SessionHelper construction
     *
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     */
    public function __construct(FrontendSessionStorageFactoryContract $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;
    }
    
    /**
     * Set the session value
     *
     * @param string $key  Key of saved information
     * @param mixed $value  Information to save
     *
     * @return void
     */
    public function setValue(string $key, $value)
    {
        $this->sessionStorage->getPlugin()->setValue('NetsEasyPayment_'.$key, $value);
    }
    
    /**
     * Get the session value
     *
     * @param string $key  Key of saved information
     *
     * @return mixed
     */
    public function getValue(string $key)
    {
        return $this->sessionStorage->getPlugin()->getValue('NetsEasyPayment_'.$key);
    }

        /**
     * Get Order Payment Method Id
     *
     * @return int
     */
	public function getOrderMopId()
    {
        /** @var array  $order*/
        $order = $this->sessionStorage->getOrder()->toArray();
        $mop = $order['methodOfPayment'];

        if(!empty($mop))
        {
            return $mop;
        }

        return 0;
    }

    /**
     * Get the language from session
     * @return string|null
     */
	public function getLang()
	{
        $lang = $this->sessionStorage->getLocaleSettings()->language;

        if(empty($lang))
        {
            $lang = 'de';
        }

		return $lang;
	}
}
