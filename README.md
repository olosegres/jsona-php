# Jsona

Request & response body formatter, that simply allow to use JSON API format in PHP-based application. Formatting from JSON to plain PHP classes and from PHP classes to JSON, observing the specification (v1.0).

http://jsonapi.org/format/
## Shaping request and response
To get started you need to create model for your entity, that will implement `IJsonApiModel` interface and describe your entity fileds.

#### Shaping JSON for response
1. Create model builder associated with created model, that will extend abstract class `JsonApiModelBuilder` and implement `buildFromDb()` method however you want.
2. Create instance of `JsonApiResponseBuilder` and fill it with parameters, using methods `setModel()`, `setData()`, `setIsCollection()`, `setMeta()`, `setRequestedIncludes()`, `setRequestedFields()`, etc.
3. Build the `JsonApiResponse` instance, using `JsonApiResponseBuilder->buid()`
4. Call `send()` method on created instance of `JsonApiResponse` to print ready JSON API formatted response.

Simple example:
```
$Model = new JsonApiImplementedModel;
$data = []; //  Any data that model builder expect

$ResponseBuilder = new Jsona\Http\Response\JsonApiResponseBuilder;
$ResponseBuilder->setModel($Model);
$ResponseBuilder->setData($data);

$ResponseBuilder->build()->send();
```

Example for collection with included, fields, meta and headers:
```
$InstituteProvider = new sj\studentsapi\services\institute\InstituteProvider;

$data = $InstituteProvider->getInstitutes([], 2, 0); //  Any data that model builder expect
$Model = new sj\studentsapi\models\InstituteFormModel;

$ResponseBuilder = new Jsona\Http\Response\JsonApiResponseBuilder;
$ResponseBuilder->setIsCollection(true);
$ResponseBuilder->setData($data);
$ResponseBuilder->setModel($Model);

$ResponseBuilder->setMeta(['somevar' => 'someval']);
$ResponseBuilder->setRequestedIncludes(['town']);
$ResponseBuilder->setRequestedFields(['towns' => ['name_accusative']]);

$ResponseBuilder->setHeaders('206 Partial Content');

$ResponseBuilder->build()->send();
```

#### Shaping PHP-model from JSON in body of request
1. Create instance of `JsonApiRequestBuilder`
2. Call `setRequestBody()` with JSON from body in request and `setModel()` with empty instance of early created `JsonApiImplementedModel`.
3. Call `build()` on it, and you'll get built `JsonApiRequest` with built `JsonApiBody` and filled `JsonApiImplementedModel`.

Example:
```
$RequestBuilder = Jsona\Http\Request\JsonApiRequestBuilder;
$RequestBuilder->setRequestBody(Yii::app()->request->getRawBody());
$RequestBuilder->setModel(new JsonApiImplementedModel);
$Request = $RequestBuilder->build();

$Model = $Request->getModel(); // get filled with data model
$meta = $Request->getBody()->getMeta(); // get meta data in array
```

## Description of classes and methods

#### JsonApiResponseBuilder
Can build an entire response from server, using next methods:
- `setModel($Model)` - to set empty empty implementation of `IJsonApiModel`, associated with entity that will responsed.
- `setData($data)` - to set prepared or raw data (from DB or any service layer), that will be using in your implementation of `JsonApiModelBuilder` for build item or collection with implementations of `IJsonApiModel`.
- `setIsCollection($boolean)` - to set what should be built, collection or item.
- `setMainArguments($Model, $data, $boolean)` - alternative to all three methods, that described above (for short call).
- `setRequestedFields($array)` - to restrict fields of entities. Use it, if request was with query parameter `fields`. It should be two-dimensional associative array, for example: `$array = ['entity1'=>['field1', 'field2'], 'entity2'=>['field1']]`.
- `setRequestedIncludes($array)` - to add relation entities data in response. Use it, if request was with query parameter `include`. It should be multi-dimensional associative array, with relation names in keys, for example: `$array = ['relation1' => true, 'relation2' => ['subRelation1' => true]`.