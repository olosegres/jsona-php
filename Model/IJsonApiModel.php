<?php namespace Jsona\Model;

/**
 * IJsonApiModel
 */
interface IJsonApiModel
{
    public function getEntityId();
    public function getEntityType();
    public function getAttributes();
    public function getRelationship($name);
    public function getRelationshipModels();

    public function setEntityId($id);
    public function setAttributes($attributes);

    /**
     * setRelationship()
     * @param string $relationName
     * @param IJsonApiModel|array[IJsonApiModel] $relation
     */
    public function setRelationship($relationName, $relation);
}