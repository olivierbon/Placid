Once you have set up your request in the admin area you'll be able to display the response in your templates using the tags below.

You can specify custom parameters, just be aware that these new parameters will completely override any specified in the admin area

``` html
{% set options = {
	query : { 'limit':2, 'sort':asc },
	segments : 'profile/' ~ username ~ '/posts'
 	cache : false,
 	duration : 3200,
 	method : 'GET'
} %}

{% set feed = craft.placid.request('newsFeed', options ) %}

{% for article in feed %}
	{{ article.title }}
{% endfor %}
```

### Parameters

- `cache` _(bool)_ - Whether or not to cache the request, default is true
- `duration` _(number)_ - The length of time in seconds to cache the request, default is whatever is in your default config
- `query` _(array)_ - An array of key/value pairs to set in the query string
- `method` _(string)_ - What method to use for the request, default is `GET`
- `segments` _(string)_ - The segments to use in the request, overrides any set in admin

### Caching
Placid will cache all requests by default. You can turn this off in the settings.  
You can tell Placid not to cache a particular request by setting cache to false in the template. This works the other way round as well.

## AJAX Requests
As of version 1.5.0 you can use AJAX to get requests, you should consider this in Beta at the moment, but I will be working on improving and adding functionality in time.

``` html
$.get('{{ actionUrl('placid/requests/request', { handle : 'REQUEST_HANDLE'}) }}', function(response){
		//.. do some jquery stuff with response
});
```
