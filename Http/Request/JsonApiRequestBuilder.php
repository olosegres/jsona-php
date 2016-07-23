<?php namespace Jsona\Http\Request;

use Jsona\Http\JsonApiBody;
use Jsona\Model\IJsonApiModel;

class JsonApiRequestBuilder
{

    protected $requestBody;
    protected $requestMethod;
    protected $Model;
    protected $includeString;

    public function setRequestBody($requestBody)
    {
        $this->requestBody = @json_decode($requestBody, true);
    }

    public function setModel(IJsonApiModel $Model)
    {
        $this->Model = $Model;
    }

    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    public function setIncludeString($includeString)
    {
        $this->includeString = $includeString;
    }

    public function setFieldsString($fields)
    {
        $this->fields = $fields;
    }

    public function build()
    {

        $Body = $this->buildBody();

        $Request = new JsonApiRequest;
        $Request->setRequestMethod($this->requestMethod);
        $Request->setBody($Body);

        if ($include = $this->buildInclude()) {
            $Request->setInclude($include);
        }

        if ($Model = $this->buildModel($Body)) {
            $Request->setModel($Model);
        }

        return $Request;
    }

    public function buildBody()
    {
        $Body = new JsonApiBody;


        if (!empty($this->requestBody['data'])) {
            $Body->setData($this->requestBody['data']);
        }

        if (!empty($this->requestBody['included'])) {
            $Body->setIncluded($this->requestBody['included']);
        }

        if (!empty($this->requestBody['meta'])) {
            $Body->setMeta($this->requestBody['meta']);
        }

        return $Body;
    }

    public function buildModel(JsonApiBody $Body)
    {
        $BuiltModel = null;

        if (!is_null($this->Model)) {
            $BuiltModel = $this->Model->getBuilder()->buildFromRequest($Body->getData());
        }

        return $BuiltModel;
    }

    public function buildInclude()
    {
        $include = [];
        if (!empty($this->includeString)) {
            $parts = explode(',', $this->includeString);
            foreach ($parts as $part) {
                $include = array_merge_recursive(
                    $include,
                    $this->buildIncludeTree(explode('.', $part))
                );
            }
        }
        return $include;
    }

    protected function buildIncludeTree(array $treeParts)
    {

        $tree = [];
        if (count($treeParts) > 1) {
            $tree[array_shift($treeParts)] = $this->buildIncludeTree($treeParts);
        } elseif (count($treeParts)) {
            $tree[array_shift($treeParts)] = true;
        }

        return $tree;
    }
}