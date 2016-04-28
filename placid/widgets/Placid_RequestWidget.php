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

        $pluginSettings = craft()->plugins->getPlugin('placid')->getSettings();

        $variables = array();

        try {
          $response = craft()->placid_requests->request($settings->request);
          $variables['response'] = new Placid_ResponseVariable($response);
        } catch (Exception $e) {
          PlacidPlugin::log($e->getMessage(), LogLevel::Error);
          $variables['response'] = null;
        }

        $path = craft()->path->getSiteTemplatesPath();
        craft()->path->setTemplatesPath($path);

        try {
            $body = craft()->templates->render($pluginSettings->widgetTemplatesPath . $settings->template, $variables);
        } catch (TemplateLoaderException $e) {
            PlacidPlugin::log("Unable to load template {$path}");
            $body = '<span class="error">Unable to load template, check logs for details</span>';
        }

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
        $pluginSettings = craft()->plugins->getPlugin('placid')->getSettings();

        // Get placid requests and send them to the widget settings
        $requests = craft()->placid_requests->findAllRequests();

        $requestsArray = array('' => 'No request selected');

        foreach($requests as $request)
        {
            $requestsArray[$request->handle] = $request->name;
        }

        $templatesPath = craft()->path->getSiteTemplatesPath() . $pluginSettings->widgetTemplatesPath;

        $templates = IOHelper::getFolderContents($templatesPath, TRUE);

        $templatesArray = array('' => Craft::t('No template selected'));

        if(!$templates)
        {
            $templatesArray = array('' => 'Cannot find templates');
            Craft::log('Cannot find templates in path "' . $templatesPath .'"', LogLevel::Error);
        }
        else
        {
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
