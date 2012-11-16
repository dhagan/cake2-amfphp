cake2-amfphp
============

Migrate http://carrotplant.com/en/blog/cpamf-v011-released v0.12 to cakephp 2.3.x

use the endpoint http://localhost/cpamf/cpamf/gateway 

Migration - This is what I did
-------------------------------
I had a working copy in cakephp 1.5 and then migrated it to 2.3.x, I debugged the working 1.5 flow until 
I could get the 2.3.x to work.  The NetBeans 7.x debugger was extremely useful.

Apologies up front for the spotty notes and lack of checkins along the way. I'm pretty certain the 
upper casing of file names would have cause havoc with git on Win32.  
The first thing I did was camelized the models and controllers.

I did this project on spec, and not yet proven out for any of my paying clients.

Here are some of the changes
----------------------------

Used cake bake to create the plugin directory structure

# web/app/Config/bootstrap.php
did I add this or did cake bake plugin, sorry, can't recall 
 web/app/Config/bootstrap.php:: CakePlugin::load('Cpamf', array('bootstrap' => false, 'routes' => false));


# web/app/Plugin/Cpamf/Controller/CpamfController.php::gatewway()
  function gateway()
	{
	    // DJH removed 'php' from cake_gateway
	    App::import('Vendor', 'Cpamf.amfphp' . DS . 'cake_gateway');
	}


# app/Config/bootstrap


// Path to the application's controllers directory.

  define('CONTROLLERS', APP.'controllers'.DS);


# web/app/Plugin/Cpamf/Vendor/amfphp/core/cakeamf/app/CakeAction.php

search for comments // DJH
path changes
changed underscore() -> camelize() in 2 places 

# browser - broken

# xdebug - works with NetBeans 7
