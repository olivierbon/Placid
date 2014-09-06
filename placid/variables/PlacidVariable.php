<?php
namespace Craft;



class PlacidVariable
{

    public function getToken($provider)
    {
        return craft()->placid_requests->getToken($provider);
    }
    public function get($handle, $options = array() )
    {
        return craft()->placid_requests->findRequestByHandle($handle, $options);
    }
    public function account($handle)
    {
        return craft()->oauth->getAccount($handle, $handle . '.system');
    }
    public function getaccessTokens()
    {
        return craft()->placid_token->getAllTokens();
    }
    public function getAllRequests()
    {
        return craft()->placid_requests->getAllRequests();
    }
    public function token($provider) {
        return craft()->placid_requests->getToken($provider);
    }
    public function request($id)
    {
        return craft()->placid_requests->findRequestById($id);
    }
    public function selectTokens()
    {
        $tokens = craft()->placid_token->getAllTokens();
        $values[null] = 'None';
        foreach($tokens as $key => $value) {
            $values[$value['id']] = ucfirst($value['name']);
        }
        return $values;
    }
    public function getProviders()
    {
        $twitter = craft()->oauth->getProvider('twitter');
        $values = array(null => 'None', $twitter['handle'] => $twitter['name']);
        return $values;
    }
}