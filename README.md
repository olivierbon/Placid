# README

Placid is a Craft plugin which makes it easy to use REST services in your twig templates, whether thats getting a twitter feed, showing off dribbble shots or getting the weather, Placid has you covered.

## Installing / Updating

**Installing**
- Download and unzip
- Upload the placid directory to craft/plugins
- Install in admin area

**Updating**
- Download and unzip
- Replace placid directory in craft/plugins
- Refresh admin section and run the update

## Basic template example

This example assumes you have a twitter request set up and authenticated using OAuth, for other examples [see here](http://alecritson.co.uk/documentation/placid/examples/weather-api)

**craft.placid.request(requestHandle)**  
This is the main placid variable, use this to get the data from the request.

    {% set timeline = craft.placid.request('twitterFeed') %}
    {% for tweet in timeline %}
      {{ tweet.text }}
    {% endfor %}
    
For full instructions on how to use, [refer to docs](http://alecritson.co.uk/documentation/placid/introduction)
