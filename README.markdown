Control HTTP cache headers on a per-Symphony-page basis.

## Usage

Once you have installed Page HTTP Caching, visit the Symphony CMS Preferences page and select the HTTP caching settings that your pages should use by default.

The `max-age` setting (in seconds) determines how long a browser or caching proxy may serve a page from its cache instead of making a new HTTP request to the origin server. You will want to tailor this value to your website’s content, users and webserver performance considerations.

You may also control these same settings per-Symphony-page if required when editing pages.

If your page includes sensitive data, it is strongly recommended not to allow caching proxies to cache.

### Symphony authors and admins

The HTTP caching header will not be applied if a Symphony CMS author or admin is logged in so that content editors always see the latest content.

## Guidelines and information

The default Symphony CMS `Cache-Control` response header directives for frontend pages (`no-cache, must-revalidate, max-age=0`) indicate that the web browser should re-request pages from the origin server. This is a good default, and ensures that the most recent dynamic content is displayed on each page load.

For websites with content that is not updated extremely frequently, it may be preferable to indicate that pages can be cached by web browsers and, optionally, by intermediary proxy caches, for a certain length of time. Doing so will allow pages to be displayed without HTTP requests re-hitting the origin webserver, potentially saving valuable CPU and memory resources. Additionally, the time taken for pages to be displayed in-browser can be substantially decreased, resulting in more of an instantaneous feel to the visitor.

This extension enables HTTP caching by allowing control of the following directives in the HTTP `Cache-Control` response header:

 * intermediary proxy caches (`public` to allow or `private` to disallow)
 * `max-age` (in seconds)

An example `Cache-Control` header value allowing for browsers and proxy caches to respond with a cached page for one hour: `public, max-age=3600`.

### Recommended reading

These articles are helpful in choosing caching methods and deciding on which HTTP cache settings to use.

[Caching Tutorial for Web Authors and Webmasters](http://www.mnot.net/cache_docs/)

[Increasing Application Performance with HTTP Cache Headers](https://devcenter.heroku.com/articles/increasing-application-performance-with-http-cache-headers)

[Cache Control Directives Demystified](http://palizine.plynt.com/issues/2008Jul/cache-control-attributes/)

[A Beginner's Guide to HTTP Cache Headers](http://www.mobify.com/blog/beginners-guide-to-http-cache-headers/)

[Symphony CMS: A guide to caching extensions](http://getsymphony.com/learn/articles/view/a-guide-to-caching-extensions/)
