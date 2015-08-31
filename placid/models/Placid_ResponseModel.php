<?php
namespace Craft;

class Placid_ResponseModel extends BaseModel
{
    public function __toString()
    {
        return $this->body;
    }
    protected function defineAttributes()
    {
        return array(
            'status'    => AttributeType::Number,
            'body' => AttributeType::String,
            'url' => AttributeType::String,
            'request' => AttributeType::String
        );
    }
}