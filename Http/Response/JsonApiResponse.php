<?php namespace Jsona\Http\Response;

class JsonApiResponse
{
    
    protected $httpCode;
    protected $headers;
    protected $body;


    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }


    public function send()
    {
        $this->sendHeaders();
        $this->sendBody();
    }

    public function sendHeaders()
    {
        if (!empty($this->httpCode)) {
            header($this->httpCode);
        } else {
            header('HTTP/1.1 200 OK');
        }

        if (is_array($this->headers)) {
            foreach ($this->headers as $name => $value) {
                header($name.': '.$value);
            }
        }
    }
    
    public function sendBody()
    {
        echo $this->body;
    }
}