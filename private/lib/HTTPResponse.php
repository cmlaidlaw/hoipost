<?php

class HTTPResponse {
  
  private $codes;
  private $code;
  private $acceptType;
  private $location;
  
  public function __construct() {
    
    $this->code = false;
    $this->acceptType = false;
    $this->location = false;
    
    $this->codes = Array(
      100 => 'Continue',
      101 => 'Switching Protocols',
      200 => 'OK',
      201 => 'Created',
      202 => 'Accepted',
      203 => 'Non-Authoritative Information',
      204 => 'No Content',
      205 => 'Reset Content',
      206 => 'Partial Content',
      300 => 'Multiple Choices',
      301 => 'Moved Permanently',
      302 => 'Found',
      303 => 'See Other',
      304 => 'Not Modified',
      305 => 'Use Proxy',
      307 => 'Temporary Redirect',
      400 => 'Bad Request',
      401 => 'Unauthorized',
      402 => 'Payment Required',
      403 => 'Forbidden',
      404 => 'Not Found',
      405 => 'Method Not Allowed',
      406 => 'Not Acceptable',
      407 => 'Proxy Authentication Required',
      408 => 'Request Timeout',
      409 => 'Conflict',
      410 => 'Gone',
      411 => 'Length Required',
      412 => 'Precondition Failed',
      413 => 'Request Entity Too Large',
      414 => 'Request-URI Too Long',
      415 => 'Unsupported Media Type',
      416 => 'Request Range Not Satisfiable',
      417 => 'Expectation Failed',
      500 => 'Internal Server Error',
      501 => 'Not Implemented',
      502 => 'Bad Gateway',
      503 => 'Service Unavailable',
      504 => 'Gateway Timeout',
      505 => 'HTTP Version Not Supported'
    );
    
  }
  
  public function ready() {
    
    if ($this->code !== false) {
      return true;
    }
    return false;
    
  }
  
  public function generateXML($input) {
    
    $output = new SimpleXMLElement("<?xml version=\"1.0\"?><messages></messages>");
    
    foreach($input['messages'] as $key => $value) {
      $node = $output->addChild("message");
      $node->addChild("id", $key);
      foreach ($input['messages'][$key] as $elementKey => $elementValue) {
        $node->addChild($elementKey, $elementValue);
      }
    }
    return $output->asXML();
    
  }
  
  public function MIMEAcceptType () {
      return $this->acceptType;
  }
  
  public function setMIMEAcceptType($type) {
    
    $this->acceptType = $type;
    return true;
    
  }
  
  public function setHTTPCode($code) {
    
    if (isset($this->codes[(int) $code])) {
      $this->code = (int) $code;
      return true;
    }
    return false;
    
  }
  
  public function redirect($location) {
    //only set the location header if the request's accept-type is "text/html"
    //this way we can gracefully handle old-school http forms while allowing
    //ajax requests to do their own thing based on the original error code
    if ($this->acceptType === 'text/html') {
      $this->location = BASE_PATH . $location;
    }
  }
  
  public function send($data) {
    
    //status code
    if (!$this->code) {
      $this->code = 200;
    }
    
    header('HTTP/1.1 ' . $this->code . ' ' . $this->codes[$this->code]);
    
    if ($this->location) {
      
      header('Location: ' . $this->location);
      
    } else {
    
      //prevent cache
      header('Cache-Control: no-cache');
      
      if ($data !== null) {
        
        //if there's data, then append that too
        
        //content type matches requested type for well-formed requests
        if ($this->acceptType === 'application/xml') {
          
          $data = $this->generateXML($data);
          header('Content-Type: application/xml');
          //added explicit content-length header so that the ajax progress bar will work
          //cml, 10/16/2012
          header('Content-Length: ' . strlen($output));
          echo $data;
                  
        } else {
          
          $data = json_encode($data);
          header('Content-Type: application/json');
          //added explicit content-length header so that the ajax progress bar will work
          //cml, 10/16/2012
          header('Content-Length: ' . strlen($data));
          echo $data;
          
        }
        
      }
      
    }
  
  }
  
}