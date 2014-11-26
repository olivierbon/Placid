# README

Placid is a Craft plugin which makes it easy to use REST services in your twig templates, whether thats getting a twitter feed, showing off dribbble shots or getting the weather, Placid has you covered.

## Installing / Updating

**Installing**
- [Download the latest release](https://github.com/alecritson/Placid/archive/v1.2.5.zip) and unzip
- Upload the placid directory to craft/plugins
- Install in admin area

**Updating**
- [Download the latest release](https://github.com/alecritson/Placid/archive/v1.2.5.zip) and unzip
- Replace placid directory in craft/plugins
- Refresh admin section and run the update

**Whats new in `1.2.5`**
- [You can now make requests via AJAX](http://alecritson.co.uk/documentation/placid/templating/make-an-ajax-request)
- [There is an `onBeforeRequest` Event](http://alecritson.co.uk/documentation/placid/events/onbeforerequest)
- [There is an `onAfterRequest` Event](http://alecritson.co.uk/documentation/placid/events/onafterrequest)
- You can now specify the cache duration using the `duration` config in your template
- The way Placid works has been rewritten for better compatibility with Guzzle
- There is a new tag, `craft.placid.request(handle)` (does the same as .get() but will be more futureproof)
- You can now set the `method` of the request (templates only right now)
- Failed requests are handled gracefully and don't stop templates loading

**Changes in `1.2.5`**
- Segments defined in the template will now override the segments set in the admin area

## Basic template example

This example assumes you have a twitter request set up and authenticated using OAuth, for other examples [see here](http://alecritson.co.uk/documentation/placid/examples/weather-api)

**craft.placid.request(requestHandle)**  
This is the main placid variable, use this to get the data from the request.

    {% set timeline = craft.placid.request('twitterFeed') %}
    {% for tweet in timeline %}
      {{ tweet.text }}
    {% endfor %}
    
For full instructions on how to use, [refer to docs](http://alecritson.co.uk/documentation/placid/introduction)
