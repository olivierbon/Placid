Once you have installed Placid you will be able to access it's area via the navigation at the top of the admin area.

![Placid main](http://alecritson.co.uk/images/content/projects/placid-requests-1.jpg)

Placid comes preloaded with a dribbble shots api request which you can use straight away. For more info on using Placid in your templates, refer to [making requests](templating/making-requests).

Click **Add request** to create a new request.

If you are using the OAuth plugin and you have set up your providers, you will see an Authentication drop down, where you will be able to select how you wish to authenticate the request.

- **Name** - The name of your request
- **Handle** - The handle used in templates (self generating)
- **Url** - This is the full url of the API you are targeting, including any file extentions i.e. .com/1.1/statuses/mentions_timeline.json not .com/1.1/statuses/mentions_timeline
- **Params** - List params, each row is a new key and value which gets attached to each request

Once you have filled out the required fields, click save and it will appear in the listing

Next, see Getting requests