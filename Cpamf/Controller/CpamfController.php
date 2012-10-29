<?php
/**
 * Default controller for the amf plugin. Imports the amfphp vendor.
 * 
 * @author Daniel Verner
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright (c) 2009 CarrotPlant Ltd.
 * @package flashservices
 * @subpackage 
 * @version $Id: cpamf_controller.php 12 2009-04-02 12:41:02Z daniel.verner $
 *  
 */

// DJH changed case
define( "SERVICE_BROWSER_PATH", ROOT . DS . APP_DIR . DS . "Plugin" . DS . 
								"Cpamf" . DS . "Vendor" . DS . "amfphp" . DS . 
								"browser" . DS  );

class CpamfController extends CpamfAppController {

	var $name = 'Cpamf';
	var $autoRender = false;
	var $layout = "blank";
	var $helpers = array( "Html" );
	
	function index() {
	}
	
	/**
	 * Gateway action serves the request from flash/flex
	 */
	function gateway()
	{
	    error_log('web_ioi::CpamfController::gateway()');
	    // DJH removed 'php' from cake_gateway
	    App::import('Vendor', 'Cpamf.amfphp' . DS . 'cake_gateway');
	}

	/**
	 * Strips slashes from fileName so we can read file from the specified folder
	 * and check file exists.
	 * 
	 * @param string $fileName
	 * @return mixed File name without slashes, or false if file not exists
	 */
	private function _checkFile( $fileName = false )
	{
		if( Configure::read() != 0 )
		{
			$fileName = str_replace( "/", "", $fileName );
			$fileName = str_replace( "\\", "", $fileName );

			if( file_exists( SERVICE_BROWSER_PATH . $fileName ) )
			{
				return $fileName;	
			}
			else
			{
				return false;
			}
		}		
	}

	/**
	 * Browser action loads the service browser (in this case controller browser)
	 * This works only if the cakePHP is configured in debug mode,
	 * else throws 404 error.
	 */	
	function browser( $fileName = false )
	{
	    //echo SERVICE_BROWSER_PATH;
		if( Configure::read() == 0 )
		{
			$this->cakeError('error404');
		}

		if( $fileName == "index" )
		{
			$this->autoRender = true;
			
		}
		else
		{
			$this->autoRender = true;
			$this->view = 'Media';

			$fileName = $this->_checkFile( $fileName );

			if( $fileName === false )
			{
				$this->cakeError('error404');
			}
			
			$params = array(
				'id' => $fileName,
				'name' => pathinfo  ( $fileName, PATHINFO_FILENAME ),
				'download' => false,
				'extension' => pathinfo( $fileName, PATHINFO_EXTENSION ),
				'path' => SERVICE_BROWSER_PATH
		 	);
	 	
		 	$this->set($params);			
		}
	}

}
?>