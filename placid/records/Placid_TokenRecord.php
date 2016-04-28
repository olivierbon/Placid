<?php

namespace Craft;

class Placid_TokenRecord extends BaseRecord
{
  public function getTableName()
  {
    return 'placid_accesstokens';
  }

  protected function defineAttributes()
    {
        return array(
            'name' => array(AttributeType::String, 'required' => true),
            'forceQuery' => array(AttributeType::Bool, 'required' => true, 'default' => false),
			      'encoded_token' => array(AttributeType::Mixed, 'required' => true)
        );
    }
 
  public function create()
  {
    $class = get_class($this);
    $record = new $class();

    return $record;
  }

}