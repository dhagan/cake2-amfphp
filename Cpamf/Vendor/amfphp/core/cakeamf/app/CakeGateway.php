<?php
/**
 * Extended version of the amf gateway, for using amfphp functions with CakePHP,
 * rpc call of cake controllers. 
 * 
 * @license 
 * @copyright (c) 2009 carrotplant.com
 * @package flashservices
 * @subpackage cakeamf/app
 * @author Daniel Verner
 * @version $Id: CakeGateway.php 12 2009-04-02 12:41:02Z daniel.verner $
 */

/**
 * AMFPHP_BASE is the location of the flashservices folder in the files system.  
 * It is used as the absolute path to load all other required system classes.
 * 
 * @author Daniel Verner
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright (c) 2009 CarrotPlant Ltd.
 * @package cpamf
 * @subpackage filters
 * @version $Id: CakeGateway.php 12 2009-04-02 12:41:02Z daniel.verner $ 
 * 
 */
define("BASE_INCLUDE", realpath(dirname(dirname(dirname(__FILE__)))) . "/");

// To prevent warning, AMFPHP_BASE is defined in Gateway.php
require_once( BASE_INCLUDE . "amf/app/Gateway.php");

/**
 * required classes for the application
 */
require_once(AMFPHP_BASE . "shared/app/Constants.php");
require_once(AMFPHP_BASE . "shared/app/Globals.php");
if(AMFPHP_PHP5)
{
	require_once(AMFPHP_BASE . "shared/util/CompatPhp5.php");
}
else
{
	require_once(AMFPHP_BASE . "shared/util/CompatPhp4.php");
}

require_once(AMFPHP_BASE . "shared/util/CharsetHandler.php");
require_once(AMFPHP_BASE . "shared/util/NetDebug.php");
require_once(AMFPHP_BASE . "shared/util/Headers.php");
require_once(AMFPHP_BASE . "shared/exception/MessageException.php");
require_once(AMFPHP_BASE . "shared/app/BasicActions.php");
require_once(AMFPHP_BASE . "amf/util/AMFObject.php");
require_once(AMFPHP_BASE . "amf/util/WrapperClasses.php");
require_once(AMFPHP_BASE . "amf/app/Filters.php");
require_once(AMFPHP_BASE . "amf/app/Actions.php");

require_once(AMFPHP_BASE . "cakeamf/app/CakeActions.php");


class CakeGateway extends Gateway
{
	/**
	 * Create the chain of actions
	 * Override the default action chain, use the cake actions
	 */
	function registerActionChain()
	{
		$this->actions['adapter'] = 'cakeAdapterAction';
		$this->actions['class'] = 'cakeClassLoaderAction';
		$this->actions['security'] = 'securityAction';
		$this->actions['exec'] = 'executionAction';
	}
}
?>