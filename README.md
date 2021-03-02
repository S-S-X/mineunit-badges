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

## HTTP API

Retrieve data:

`GET /account/project/id` returns 302 redirect to https://shields.io service which provides actual badges.

Update or create badge configuration:

POST request with badge definition as JSON in request body:

`POST /account/project/id` returns 201 when update succeed, otherwise some vague error code.
```javascript
{
	"value": "99%",      // Required, right hand side text for badge.
	"label": "Coverage", // Optional, left hand side text for badge. Default: "Coverage".
	"color": "D0F055"    // Optional, background color for badge. Default: "D0F055".
}
```

Note: Application itself actually uses query parameters but when installing app you really should rewrite requests to
1. get rid of weirdo characters that increase risk of mistakes.
2. simply keep it clean for users.

## Installation PHP-FPM with HAProxy

This assumes that PHP-FPM belongs to group www-data, different Linux distros have different groups.
Adjust groups and directories as needed.
Works just fine with Apache or NGINX, basic setup is similar for those.

1. Basic directory structure:
	```
	mkdir /var/www/mineunit-badges
	mkdir /var/www/mineunit-badges/tokens
	mkdir /var/www/mineunit-badges/tmp
	```
2. Copy sources to `/var/www/mineunit-badges/public`, this should include empty `data` directory.
3. Set permissions
	```
	setfacl -m d:g:www-data:rwx /var/www/mineunit-badges/tokens
	setfacl -m g:www-data:rwx /var/www/mineunit-badges/tokens
	setfacl -m d:g:www-data:rwx /var/www/mineunit-badges/public/data
	setfacl -m g:www-data:rwx /var/www/mineunit-badges/public/data
	chmod 1777 /var/www/mineunit-badges/tmp
	```
4. Edit `config.php` to set CLIENT_ID and possibly change directories if needed.

This guide is not about your server security, adjust permissions, owners, groups, security policies etc. as needed.

#### HAProxy backend:

Use your imagination for HAProxy frontend...
This thing assumes that you're not doing anything stupid and that you're running everything in chroot.

```haproxy
backend mineunit-badges
	acl badge-query path_reg ^/[-_A-Za-z0-9]+/[-_A-Za-z0-9]+/[-_A-Za-z0-9]+$
	acl badge-auth path_reg ^/auth$
	http-request deny if ! badge-query ! badge-auth
	http-request replace-uri ([^/:]*://[^/]*)?/([-_A-Za-z0-9]+)/([-_A-Za-z0-9]+)/([-_A-Za-z0-9]+)$ \1/index.php?account=\2&project=\3&id=\4 if badge-query
	http-request replace-path . /auth.php if badge-auth
	use-fcgi-app php-fpm
	server php-fpm /run/mineunit-badges.sock proto fcgi

fcgi-app php-fpm
	log-stderr global
	docroot /public
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
No idea how good uptime 000webhost has or when they'll decide that my app is shit and delete it...

By reading sources you can find out how to get API key to access service.

#### Known problems

App does not actually care what project you modify, just that you have access. This basically makes everyone admin.
This check however is fairly simple to implement but I'll return to this after some testing to see if
badges app is actually useful.

Dots will be cleaned from account, project and id. This does not prevent using it with names containing `.` but if
dots will be allowed later then badges added with dot in any part of name will break until added again.
