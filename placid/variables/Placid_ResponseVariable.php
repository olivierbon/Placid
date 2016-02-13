<?php
namespace Craft;

class Placid_ResponseVariable
{
	protected $statusCode;
	public $data;
	protected $response;

    public function __construct(\Guzzle\Http\Message\Response $response)
    {
    	$this->statusCode = $response->getStatusCode();
    	$this->response = $response;
    	$this->data = $this->_getDataArray();
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

    	$responseBody = $this->response->getBody();

    	$contentType = preg_match('/.+?(?=;)/', $responseBody->getContentType(), $matches);

	    $contentType = implode($matches, '');
	    
	    try {   
	      if($contentType == 'text/xml')
	      {
	        $output = $this->response->xml();
	      }
	      else
	      {
	        $output = $this->response->json();
	      }
	    } catch (\Guzzle\Common\Exception\RuntimeException $e) {
	      PlacidPlugin::log($e->getMessage(), LogLevel::Error);
	      $output = null;
	    }
	    return $output;
    }
}