<?php namespace Jsona\Http;

class JsonApiBody
{

    protected $meta;
    protected $data;
    protected $included;
    protected $errors;
    protected $links;


    public function setMeta($meta)
    {
        $this->meta = $meta;
    }


    public function setData($data)
    {
        $this->data = $data;
    }

    public function setIncluded($included)
    {
        $this->included = $included;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function getMeta()
    {
        return $this->meta;
    }


    public function getData()
    {
        return $this->data;
    }

    public function getIncluded()
    {
        return $this->included;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Устанавливает данные в links
     * @param $links
     * @return $this
     */
    public function setLinks($links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * Возвращает данные из links
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }


    public function getContent()
    {
        $body = array();

        if ($this->errors) {
            $body['errors'] = $this->errors;
        } else {
            $body['data'] = $this->data;


            if ($this->included) {
                $body['included'] = $this->included;
            }
        }


        if (!empty($this->links)) {
            $body['links'] = $this->links;
        }

        if ($this->meta) {
            $body['meta'] = $this->meta;
        }

        

        return $body;
    }
}