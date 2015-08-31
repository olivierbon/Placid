<?php

namespace Craft;

class Placid_RequestsRecord extends BaseRecord
{
  public function getTableName()
  {
    return 'placid_requests';
  }

  protected function defineAttributes()
    {
        return array(
            'name' => array(AttributeType::String, 'required' => true),
            'handle' => array(AttributeType::String, 'required' => true, 'unique' => true),
            'url' => array(AttributeType::String, 'required' => true),
            'oauth' => AttributeType::String,
            'params' => array(AttributeType::Mixed, 'required' => false),
            'headers' => array(AttributeType::Mixed, 'required' => false),
            'cache' => array(AttributeType::Bool, 'required' => true, 'default' => true),
            'cache_id' => array(AttributeType::Mixed, 'required' => false)
        );
    }
   public function defineRelations()
  {
    return array(
      'token' => array(static::BELONGS_TO, 'Placid_TokenRecord', 'required' => false, 'onDelete' => static::SET_NULL)
    );
  }

}