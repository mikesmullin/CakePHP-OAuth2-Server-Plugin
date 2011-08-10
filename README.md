CakePHP OAuth2 Server Plugin by Mike Smullin <mike@smullindesign.com>
============

** Host your own OAuth2 API Server Authentication system like Facebook, Twitter, etc. **

Pre-requisites
------------

* CakePHP User Model defined in app
* No conflict with using CakePHP Auth Component in plugin
* CakePHP Migrations plugin installed

Installation & Usage
------------

Place this directory in your plugins dir:

    git submodule add git://github.com/mikesmullin/CakePHP-OAuth2-Server-Plugin.git ./app/plugins/o_auth2_server/

Download the latest version of Tim Ridgley's oauth2-php into `./app/plugins/oauth/vendors/oauth2-php/`, as well:

    git submodule update --init --recursive

Add this line to your ./app/config/routes.php:

    // include CakePHP-OAuth2-Server-Plugin routes
    require_once App::pluginPath('OAuth2Server') .'config'. DS .'routes.php';

Run this plugin's migrations (requires CakePHP Migrations plugin by CakeDC)

    cake migration -plugin OAuth2Server

Customize the file `./app/plugins/oauth/config/oauth.php` to fit your use case.

Add this plugin's OAuth2 component to your AppController:

    var $components = array('OAuth2Server.OAuth2');

Add this override to your AppController:

    /**
     * Override isAuthorized() callback.
     * Disables placeholder error and changes default to null,
     * which has a special meaning for OAuth2Server plugin.
     *
     * @return Boolean
     *   null = check normally
     *   true = force allow without check
     *   false = force disallow without check
     */
    function isAuthorized() {
        return null; // check normally
    }

Authentication verification happens automatically any time getCurrentUserId() is called:

    try {
      $this->OAuth2->getCurrentUserId(true); // true is optional and default; means throw exception on failure
    } catch (Exception $e) {
      // handle problems with access_token here
    }

Credits
------------

CakePHP-OAuth2-Server-Plugin is written by Mike Smullin and is released under the WTFPL.

OAuth2-PHP is written by Tim Ridgely and licensed under MIT. see http://code.google.com/p/oauth2-php/

CakePHP Migrations is written by CakeDC. see https://github.com/CakeDC/migrations
