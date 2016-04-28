<?php
namespace Craft;

class Placid_TokenModel extends BaseModel
{
    // public function __toString()
    // {
    //     return $this->handle;
    // }
    protected function defineAttributes()
    {
        return array(
            'id'    => AttributeType::Number,
            'name' => array(AttributeType::String, 'required' => true),
            'forceQuery' => AttributeType::Bool,
            'encoded_token' => AttributeType::String
        );
    }
}