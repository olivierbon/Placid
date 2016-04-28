<?php
namespace Craft;

class Placid_ResponseVariable
{
	protected $statusCode;
	public $data;
	protected $response;

    public function __construct($response)
    {
        if($response)
        {
            $this->statusCode = $response->getStatusCode();
            $this->response = $response;
            $this->data = $this->_getDataArray();
        }
    }

    public function status()
    {
    	return $this->statusCode;
    }

    public function limit($amount = null) {
    	if(is_array($this->data))
    	{
    		$this->data = array_slice($this->data, 0, $amount, true);
    	}
    	return $this;
    }

    private function _getDataArray()
    {
	    return craft()->placid_requests->getDataFromResponse($this->response);
    }
}