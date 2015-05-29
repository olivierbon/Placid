<?php
namespace Craft;

class Placid_OAuthModel extends BaseModel
{
    public function __toString()
    {
        return $this->tokenId;
    }
    protected function defineAttributes()
    {
        return array(
            'id'    => AttributeType::Number,
            'tokenId' => AttributeType::String,
            'handle' => AttributeType::String
        );
    }
}