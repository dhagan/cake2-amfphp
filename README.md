cake2-amfphp
============

Migrate http://carrotplant.com/en/blog/cpamf-v011-released v0.12 to cakephp 2.3.x

use the endpoint http://localhost/cpamf/cpamf/gateway 

Migration - I had a working copy in cakephp 1.5 and then migrated it to 2.3.x

Apologies up front for the spoty notes and lack of checkin.  The first thing I did was camelized the models and controllers.
Sorry for not committing along the way, this was project was taken on spec, and not yet proven out.

Here are the steps 
---------------------

did I add this or did cake bake plugin, sorry, can't recall 
web/app/Config/bootstrap.php:: CakePlugin::load('Cpamf', array('bootstrap' => false, 'routes' => false));


web/app/Plugin/Cpamf/Controller/CpamfController.php::gatewway()
  function gateway()
	{
	    // DJH removed 'php' from cake_gateway
	    App::import('Vendor', 'Cpamf.amfphp' . DS . 'cake_gateway');
	}


app/Config/bootstrap


// Path to the application's controllers directory.

  define('CONTROLLERS', APP.'controllers'.DS);


web/app/Plugin/Cpamf/Vendor/amfphp/core/cakeamf/app/CakeAction.php

search for comments // DJH
path changes
changed underscore() -> camelize() in 2 places 


browser - broken

xdebug - didn't get it to work, it worked in 1.5
