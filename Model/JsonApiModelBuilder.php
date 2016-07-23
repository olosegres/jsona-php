<?php namespace Jsona\Model;

abstract class JsonApiModelBuilder
{

    protected $Model;

    /**
     * buildFromDb() - реализация заполнения модели данными из сервиса/БД
     */
    abstract public function buildFromDb($dataFromDb);

    public function __construct(IJsonApiModel $Model)
    {
        $this->Model = $Model;
    }

    public function buildFromRequest($data)
    {
        $Model = $this->getModel();
        if (!empty($data['id'])) {
            $Model->setEntityId($data['id']);
        }

        if (!empty($data['attributes'])) {
            $Model->setAttributes($data['attributes']);
        }

        if (!empty($data['relationships']) && is_array($data['relationships'])) {
            $relationshipModels = $Model->getRelationshipModels();

            foreach ($relationshipModels as $relationName => $relationModel) {
                // if there is data for existing relation
                if (isset($data['relationships'][$relationName]['data'])) {
                    if (isset($data['relationships'][$relationName]['data']['id'])) {
                        // if its not multiple relation

                        $readyRelation = $relationModel->getBuilder()->buildFromRequest($data['relationships'][$relationName]['data']);

                    } else {
                        // if it is multiple relation
                        $readyRelation = array_map(function ($relationDataItem) use ($relation) {
                            foreach ($relation as $relation) {
                                if ($relation->getType() === $relationDataItem['type']) {
                                    return $relation->getBuilder()->buildFromRequest($relationDataItem);
                                }
                            }
                        }, $data['relationships'][$relationName]['data']);
                    }

                    $Model->setRelationship($relationName, $readyRelation);
                }
            }
        }

        return $Model;
    }

    /**
     * @return IJsonApiModel
     */
    public function getModel()
    {
        return $this->Model;
    }
}
