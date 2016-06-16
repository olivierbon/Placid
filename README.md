![Repo header image](http://itsalec.co.uk/images/placid-github-header.jpg)  
![Placid Screenshot](http://itsalec.co.uk/images/placid.1.6.10.jpg)

Placid is a Craft plugin which makes it easy to use REST services in your twig templates, whether thats getting a twitter feed, showing off dribbble shots or getting the weather, Placid has you covered.

## Features

- Send `PUSH`, `POST`, `PATCH`, `GET` requests to an API
- Define your own access tokens to send with requests
- Integrates with the [Dukt OAuth plugin](https://dukt.net/craft/oauth/)
- Send requests via AJAX
- Easily retrieve information about each requests response
- Modify requests before they are sent, within your template.

## Installing / Updating

**Installing**
- [Download the latest release](https://github.com/alecritson/Placid/releases/latest) and unzip
- Upload the placid directory to craft/plugins
- Install in admin area

**Updating**
- [Download the latest release](https://github.com/alecritson/Placid/releases/latest) and unzip
- Replace placid directory in craft/plugins
- Refresh admin section and run the update

## Basic example

Assuming you had your request set up like in the example above. You just need to do this:

```twig
{% set timeline = craft.placid.request('timeline', options) %}

{# Get the http status code that was sent from Twitter #}
{{ timeline.status }}

{% for tweet in timeline.data %}
  {{ tweet.text }}
{% endfor %}
```

For full instructions on how to use, [refer to the wiki](https://github.com/alecritson/placid/wiki)


