<?php
namespace Craft;

class Placid_RequestWidget extends BaseWidget
{

    public function getName()
    {
        $label = 'Placid Request';

        try {
            $request = craft()->placid_requests->findRequestByHandle($this->settings->request);
        } catch(Exception $e)
        {
            $request = null;
        }
        if($request)
        {
            $label = $request->name;
        }

        return Craft::t($label);
    }

    public function getBodyHtml()
    {
        $settings = $this->getSettings();
        $variables = array();

        $variables['response'] = craft()->placid_requests->request($settings->request);

        $path = craft()->path->getSiteTemplatesPath();
        craft()->path->setTemplatesPath($path);

        $body = craft()->templates->render('_widgets/placid/' . $settings->template, $variables);

        $path = craft()->path->getCpTemplatesPath();
        craft()->path->setTemplatesPath($path);

        return craft()->templates->render('placid/_widgets/request/body', array(
            'body' => $body
        ));
    }
    protected function defineSettings()
    {
        
        return array(
           'request' => AttributeType::String,
           'colspan' => AttributeType::Number,
           'template' =>  AttributeType::String
        );
    }
    public function getSettingsHtml()
    {   
        $templatesPath = craft()->path->getSiteTemplatesPath() . '_widgets/placid/';

        $templates = IOHelper::getFolderContents($templatesPath, TRUE);

        $templatesArray = array('' => Craft::t('No template selected'));

        // Turn array into ArrayObject 
        $templates = new \ArrayObject($templates);

        // Iterate over template list
        // * Remove full path 
        // * Remove folders from list
        for ($list = $templates->getIterator();
        $list->valid(); $list->next()) 
        { 
            $filename = $list->current();
            
            $filename = str_replace($templatesPath, '', $filename);
            $filenameIncludingSubfolder = $filename;
            $isTemplate = preg_match("/(.html|.twig)$/u", $filename);
            
            if ($isTemplate) $templatesArray[$filenameIncludingSubfolder] = $filename;
        }

        $requests = craft()->placid_requests->getAll();

        $requestsArray = array('' => 'No request selected');

        foreach($requests as $request)
        {
            $requestsArray[$request->handle] = $request->name;
        }

        return craft()->templates->render('placid/_widgets/request/settings', array(
            'requests' => $requestsArray,
            'templates' => $templatesArray,
            'settings' => $this->getSettings()
        ));
    }
    public function getColspan()
    {
        return $this->getSettings()->colspan;
    }
}