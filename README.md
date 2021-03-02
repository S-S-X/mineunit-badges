# mineunit-badges

Mineunit badges from shields.io

Actually this has nothing to do with mineunit other than both do something with badges...

There would be better more efficient platforms for this kind of stuff
but decided to do PHP because it is very simple and runs just about everywhere.

Also I wanted to try out HAProxy fcgi-app and PHP-FPM just is simplest way to do it.

HAProxy could also directly serve content like this and only direct updates to backend app.
I might add that as option but current thing works anywhere if you can execute any
web server with PHP interpreter, probably works with any version you can easily get.

Decided to not store data to databases because data is extremely simple and flat files
are usually cheapest and fastest option for very simple storage.

No authentication available yet, probably something simple like static
tokens based auth (API keys) with header/query param options is best.

Code is kinda bad if it would be larger app but pretty straightforward and easy to follow as long as app stays small.

## PHP-FPM with HAProxy

#### HAProxy backend:

Use your imagination for HAProxy frontend...
This thing assumes that you're not doing anything stupid and that you're running everything in chroot.

```haproxy
backend mineunit-badges
	acl badge-query path_reg ^/[-_A-Za-z0-9]+/[-_A-Za-z0-9]+$
	http-request deny if ! badge-query
	http-request replace-uri ([^/:]*://[^/]*)?/([-_A-Za-z0-9]+)/([-_A-Za-z0-9]+)$ \1/index.php?account=\2&id=\3 if badge-query
	use-fcgi-app php-fpm
	server php-fpm /run/mineunit-badges.sock proto fcgi

fcgi-app php-fpm
	log-stderr global
	docroot /
	path-info ^(/.+\.php)(/.*)?$
```

#### PHP-FPM backend:

```
[mineunit-badges]
user = www-data
group = www-data
listen = /var/lib/haproxy/run/mineunit-badges.sock
listen.owner = www-data
listen.group = haproxy
chroot = /var/www/mineunit-badges
```

#### Unstable test server

Hosted for free at 000webhost. Service addrees is https://mineunit-badges.000webhostapp.com/

By reading sources you can find out how to get API key to access service.
