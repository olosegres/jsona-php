<?php namespace Jsona\Http\Response;

use Jsona\Http\JsonApiBody;
use Jsona\Model\IJsonApiModel;

class JsonApiResponseBuilder
{

    protected $httpCode;
    protected $headers;
    protected $Model;
    protected $data;
    protected $meta;
    protected $errors;
    protected $requestedIncludes;
    protected $requestedFields;
    protected $isCollection;

    public function setMainArguments(IJsonApiModel $Model, $data, $isCollection = false)
    {
        $this->setModel($Model);
        $this->setData($data);
        $this->setIsCollection($isCollection);
    }

    public function setModel(IJsonApiModel $Model)
    {
        $this->Model = $Model;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setIsCollection($isCollection)
    {
        $this->isCollection = $isCollection;
    }

    public function setRequestedFields($array)
    {
        $this->requestedFields = $array;
    }

    public function setRequestedIncludes($array)
    {
        $this->requestedIncludes = $array;
    }

    public function setMeta($array)
    {
        $this->meta = $array;
    }

    public function setErrors($array)
    {
        $this->errors = $array;
    }


    public function setHeaders($array)
    {
        $this->headers = $array;
    }

    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }

    public function appendHeaders($array)
    {
        $this->headers = $this->headers ? array_merge($this->headers, $array) : $array;
    }

    public function build()
    {

        $Body = $this->buildBody();

        $Response = new JsonApiResponse;


        if ($this->httpCode) {
            $Response->setHttpCode($this->httpCode);
        }

        if ($this->headers) {
            $Response->setHeaders($this->headers);
        }

        $Response->setBody($this->prepareBody($Body->getContent()));

        return $Response;
    }


    public function buildBody()
    {
        $Body = new JsonApiBody;

        $data = array();
        $included = array();

        if (!empty($this->errors) && count($this->errors)) {
            $Body->setErrors($this->errors);
        } elseif ($this->getModel() instanceof IJsonApiModel) {
            if ($this->isCollection()) {
                foreach ($this->getData() as $item) {
                    $ReadyModel = $this->getModel()->getBuilder()->buildFromDb($item);

                    if ($dataItem = $this->getDataByModel($ReadyModel)) {
                        $data[] = $dataItem;
                    }
                    if ($includedCollection = $this->getIncludedByModel($ReadyModel, $this->requestedIncludes)) {
                        $included = array_merge($included, $includedCollection);
                    }
                }
            } else {
                $ReadyModel = $this->getModel()->getBuilder()->buildFromDb(
                    $this->data
                );

                if ($dataItem = $this->getDataByModel($ReadyModel)) {
                    $data = $dataItem;
                }
                if ($includedCollection = $this->getIncludedByModel($ReadyModel, $this->requestedIncludes)) {
                    $included = $includedCollection;
                }
            }

            $Body->setData($data);

            if ($included = $this->prepareIncluded($included)) {
                $Body->setIncluded($included);
            }
        }

        if ($meta = $this->prepareMeta($this->meta)) {
            $Body->setMeta($meta);
        }

        return $Body;
    }


    protected function prepareBody($bodyArray)
    {
        return json_encode($bodyArray);
    }

    protected function prepareIncluded($included)
    {
        return $included;
    }

    protected function prepareMeta($meta)
    {
        return $meta;
    }

    protected function getDataByModel(IJsonApiModel $Model)
    {
        $dataPart = array(
            'id' => $Model->getEntityId(),
            'type' => $Model->getEntityType(),
            'attributes' => $this->getPreparedAttributes($Model),
        );

        if ($relationships = $this->getRelationshipsByModel($Model)) {
            $dataPart['relationships'] = $relationships;
        }

        return $dataPart;
    }

    protected function getRelationshipsByModel(IJsonApiModel $Model)
    {
        if ($relationshipModels = array_keys($Model->getRelationshipModels())) {
            $relationships = [];
            foreach ($relationshipModels as $relationName) {
                $relation = $Model->getRelationship($relationName);
                if (is_array($relation)) {
                    $relationships[$relationName] = [];

                    foreach ($relation as $RelationModel) {
                        $relationships[$relationName][] = $this->getRelationshipPart($RelationModel);
                    }
                } elseif ($relation instanceof IJsonApiModel) {
                    $relationships[$relationName] = $this->getRelationshipPart($relation);
                } else {
                    $relationships[$relationName] = new \stdClass;
                }
            }

            return $relationships;
        }
    }


    protected function getIncludedByModel(IJsonApiModel $Model, $requestedIncludes)
    {
        $included = array();

        if (is_array($requestedIncludes) && ($includeRelations = array_keys($requestedIncludes))) {
            foreach ($includeRelations as $relationName) {
                if ($relation = $Model->getRelationship($relationName)) {
                    if (is_array($requestedIncludes[$relationName])) {
                        if (is_array($relation)) {
                            foreach ($relation as $relationItem) {
                                $included = array_merge(
                                    $included,
                                    $this->getIncludedByRelation($relationItem),
                                    $this->getIncludedByModel($relationItem, $requestedIncludes[$relationName])
                                );
                            }
                        } else {
                            $included = array_merge(
                                $included,
                                $this->getIncludedByRelation($relation),
                                $this->getIncludedByModel($relation, $requestedIncludes[$relationName])
                            );
                        }
                    } else {
                        $included = array_merge(
                            $included,
                            $this->getIncludedByRelation($relation)
                        );
                    }
                }
            }
        }

        return $included;
    }

    protected function getIncludedByRelation($relation)
    {
        $included = [];

        if (is_array($relation)) {
            foreach ($relation as $RelationItem) {
                $included[$this->getIncludedKey($RelationItem)] = $this->getIncludedPart($RelationItem);
            }
        } else {
            $included[$this->getIncludedKey($relation)] = $this->getIncludedPart($relation);
        }

        return $included;
    }

    protected function getIncludedKey($Model)
    {
        return $Model->getEntityType() . $Model->getEntityId();
    }

    protected function getRelationshipPart(IJsonApiModel $RelationModel)
    {
        return array(
            'data' => array(
                'id' => $RelationModel->getEntityId(),
                'type' => $RelationModel->getEntityType(),
            ),
        );
    }

    protected function getIncludedPart(IJsonApiModel $Model)
    {
        $included = array(
            'id' => $Model->getEntityId(),
            'type' => $Model->getEntityType(),
            'attributes' => $this->getPreparedAttributes($Model),
        );

        if ($relationships = $this->getRelationshipsByModel($Model)) {
            $included['relationships'] = $relationships;
        }

        return $included;
    }

    protected function getPreparedAttributes(IJsonApiModel $Model)
    {
        $attributes = array();

        $modelType = $Model->getEntityType();

        $relationshipModels = $Model->getRelationshipModels();

        foreach ($Model->getAttributes() as $attrName => $attrValue) {
            if (empty($this->requestedFields[$modelType]) ||
                in_array($attrName, $this->requestedFields[$modelType])) {
                $attributes[$attrName] = $attrValue;
            }
        }

        return $attributes;
    }

    protected function getData()
    {
        return $this->data;
    }

    protected function getModel()
    {
        return $this->Model;
    }

    protected function isCollection()
    {
        return (bool) $this->isCollection;
    }
}