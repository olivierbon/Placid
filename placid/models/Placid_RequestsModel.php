<?php
namespace Craft;

class Placid_RequestsModel extends BaseModel
{
    public function __toString()
    {
        return $this->handle;
    }
    protected function defineAttributes()
    {
        return array(
            'id'    => AttributeType::Number,
            'name' => AttributeType::String,
            'handle' => AttributeType::String,
            'url' => AttributeType::String,
            'oauth' => AttributeType::String,
            'tokenId' => AttributeType::Number,
            'params' => AttributeType::String,
            'headers' => AttributeType::String,
            'cache_id' => AttributeType::Mixed
        );
    }
}