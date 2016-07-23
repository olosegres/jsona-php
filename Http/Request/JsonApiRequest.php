<?php namespace Jsona\Http\Request;

use Jsona\Http\JsonApiBody;
use Jsona\Model\IJsonApiModel;

class JsonApiRequest
{
    
    protected $requestMethod;
    protected $Body;
    protected $Model;

    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    public function setBody(JsonApiBody $Body)
    {
        $this->Body = $Body;
    }
    public function setModel(IJsonApiModel $Model)
    {
        $this->Model = $Model;
    }

    public function setInclude($arrayTree)
    {
        return $this->include = $arrayTree;
    }

    public function getRequestMethod()
    {
        return $this->requestMethod;
    }
    
    public function getBody()
    {
        return $this->Body;
    }

    public function getModel()
    {
        return $this->Model;
    }

    public function getInclude()
    {
        return $this->include;
    }
}