Control HTTP cache headers on a per-Symphony-page basis.

## Guidelines and information

The default Symphony CMS `Cache-Control` response header values for frontend pages indicate that the web browser should re-request pages from the origin server. This is a good default, and ensures that the most recent dynamic content is displayed on each page load.

For websites with content that is not updated extremely frequently, it may be preferable to indicate that pages can be cached by web browsers and, optionally, by intermediary caches such as web proxies, for a certain length of time. Doing so will allow pages to be displayed without HTTP requests re-hitting the origin webserver, potentially saving valuable CPU and memory resources. Additionally, the time taken for pages to be displayed in-browser can be substantially decreased, resulting in more of instantaneous feel to the visitor.

Page HTTP Caching enables HTTP caching by allowing control of the following values in the HTTP `Cache-Control` response header:

 * intermediary caches (`public` or `private`)
 * `max-age` (in seconds)

If the majority of a website’s pages include content that must be completely fresh, such as user login status, or content that is updated extremely frequently, Page HTTP Caching is likely not suitable.

### Recommended reading

These articles are helpful in choosing caching methods and deciding on which HTTP cache settings to use.

[Symphony CMS: A guide to caching extensions](http://getsymphony.com/learn/articles/view/a-guide-to-caching-extensions/)

[Caching Tutorial for Web Authors and Webmasters](http://www.mnot.net/cache_docs/)

[Increasing Application Performance with HTTP Cache Headers](https://devcenter.heroku.com/articles/increasing-application-performance-with-http-cache-headers)

[Cache Control Directives Demystified](http://palizine.plynt.com/issues/2008Jul/cache-control-attributes/)

## Usage

Once you have installed Page HTTP Caching, visit the Symphony CMS Preferences page and select the HTTP caching settings that your pages should use by default.

If your content includes sensitive data such as user information, it is recommended not to enable intermediary caches.

The `max-age` settings (in seconds) determines how long a browser may choose to serve a page from its cache instead of making an HTTP request. You will want to tailor this value to your website’s content and users.

You may also control these same settings per-Symphony-page if required when editing pages.

The HTTP caching will not be applied if a Symphony CMS author or admin is logged in; this is to allow content editors to see the latest data.
