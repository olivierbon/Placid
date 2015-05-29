<?php

namespace Craft;

class Placid_OAuthRecord extends BaseRecord
{
  public function getTableName()
  {
    return 'placid_oauth_tokens';
  }

  protected function defineAttributes()
  {
    return array(
        'handle' => array(AttributeType::String, 'required' => true, 'unique' => true),
        'tokenId' => array(AttributeType::String, 'required' => true)
    );
  }
  public function create()
  {
    $class = get_class($this);
    $record = new $class();
    return $record;
  }

}