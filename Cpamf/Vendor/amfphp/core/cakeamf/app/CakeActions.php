<?php
/**
 * Actions modify the AMF message PER BODY
 * This allows batching of calls
 * 
 * Adapter action and ClassLoaderAction are modified to handle the cakePHP
 * controllers
 * 
 * @author Daniel Verner 
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright (c) 2009 CarrotPlant Ltd.
 * @package cpamf
 * @subpackage filters
 * @version $Id: CakeActions.php 12 2009-04-02 12:41:02Z daniel.verner $
 */

/**
 * Catches any special request types and classifies as required
 */
function cakeAdapterAction (&$amfbody) {
	$baseClassPath = $GLOBALS['amfphp']['classPath'];
	$uriclasspath = "";
	$classname = "";
	$classpath = "";
	$methodname = "";
	$isWebServiceURI = false;

	$target = $amfbody->targetURI;
	if (strpos($target, "http://") === false && strpos($target, "https://") === false) { // check for a http link which means web service
		$lpos = strrpos($target, ".");
		if ($lpos === false) {
			//Check to see if this is in fact a RemotingMessage
			$body = $amfbody->getValue();
			$handled = false;
			
			$messageType = $body[0]->_explicitType;
			if($messageType == 'flex.messaging.messages.RemotingMessage')
			{
				$handled = true;
				
				//Fix for AMF0 mixed array bug in Flex 2
				if(isset($body[0]->body['length']))
				{
					unset($body[0]->body['length']);
				}
				
				$amfbody->setValue($body[0]->body);
				$amfbody->setSpecialHandling("RemotingMessage");
				$amfbody->setMetadata("clientId", $body[0]->clientId);
				$amfbody->setMetadata("messageId", $body[0]->messageId);
				
				$GLOBALS['amfphp']['lastMessageId'] = $body[0]->messageId;
				
				$methodname = $body[0]->operation;

				// Class names are CamelCased in cakePHP
				$classAndPackage = Inflector::camelize( $body[0]->source );
				if( $classAndPackage == "Amfphp.DiscoveryService" )
				{
					$classAndPackage = "DiscoveryServiceController";
					// // DJH changed case
					$baseClassPath = ROOT . DS . APP_DIR . DS . "Plugin" . DS . "Cpamf" . DS . "Controller" . DS;
				}
				$lpos = strrpos($classAndPackage, ".");
				if($lpos !== FALSE)
				{
					$classname = substr($classAndPackage, $lpos + 1);
				}
				else
				{
					$classname = $classAndPackage;
				}
				// Classes are CamelCased but files underscored in cakePHP
				// DJH use camelize() not underscore()
				$uriclasspath = Inflector::camelize( str_replace('.','/',$classAndPackage) ) . '.php';
				$classpath = $baseClassPath . $uriclasspath;
			}
			elseif($messageType == "flex.messaging.messages.CommandMessage")
			{
				if($body[0]->operation == 5)
				{
					$handled = true;
					$amfbody->setSpecialHandling("Ping");
					$amfbody->setMetadata("clientId", $body[0]->clientId);
					$amfbody->setMetadata("messageId", $body[0]->messageId);
					$amfbody->noExec = true;
				}
			}
			
			if(!$handled)
			{
				$uriclasspath = "amfphp/Amf3Broker.php";
				$classpath = $baseClassPath . "amfphp/Amf3Broker.php";
				$classname = "Amf3Broker";
				$methodname = "handleMessage";
			}
		} else {
			$methodname = substr($target, $lpos + 1);
			$trunced = substr($target, 0, $lpos);
			$lpos = strrpos($trunced, ".");
			if ($lpos === false) {
				$classname = $trunced;
				if ($classname == "PageAbleResult" && $methodname == 'getRecords') {
					$val = $amfbody->getValue();
					$id = $val[0];
					$keys = explode("=", $id);
					$currset = intval($keys[1]);
					
					$set = $_SESSION['amfphp_recordsets'][$currset];
					
					$uriclasspath = $set['class'];
					$classpath = $baseClassPath . $set['class'];
					$methodname = $set['method'];
					
					$classname = substr(strrchr('/' . $set['class'], '/'), 1, -4);
					
					//Now set args for body
					$amfbody->setValue(array_merge($set['args'], array($val[1], $val[2])));
					
					//Tell amfbody that this is a dynamic paged resultset
					$amfbody->setSpecialHandling('pageFetch');
				} 
				else if($classname == "PageAbleResult" && $methodname == 'release')
				{
					$amfbody->setSpecialHandling('pageRelease');
					$amfbody->noExec = true;
				}
				else {
					// Classes are CamelCased but files underscored in cakePHP
					// DJH use camelize() not underscore()
					$uriclasspath = Inflector::camelize( $trunced ) . ".php";
					$classpath = $baseClassPath . Inflector::camelize( $trunced ) . ".php";
				} 
			} else {
				$classname = substr($trunced, $lpos + 1);
				$classpath = $baseClassPath . str_replace(".", "/", $trunced) . ".php"; // removed to strip the basecp out of the equation here
				// Classes are CamelCased but files underscored in cakePHP
				$uriclasspath = Inflector::camelize( str_replace(".", "/", $trunced) ) . ".php"; // removed to strip the basecp out of the equation here
			} 
		}
	} else { // This is a web service and is unsupported
		trigger_error("Web services are not supported in this release", E_USER_ERROR);
	} 

	$amfbody->classPath = $classpath;
	$amfbody->uriClassPath = $uriclasspath;
	$amfbody->className = $classname;
	$amfbody->methodName = $methodname;

	return true;
} 

/**
 * Class loader action loads the class from which we will get the remote method
 */
function cakeClassLoaderAction (&$amfbody) {
	// error_log(print_r(debug_backtrace(), true));
	// error_log('web_ioi::cakeClassLoaderAction :: ' . print_r($amfbody, true));
	if(!$amfbody->noExec)
	{ 
		// change to the gateway.php script directory
		// now change to the directory of the classpath.  Possible relative to gateway.php
		$dirname = dirname($amfbody->classPath);
		if(is_dir($dirname))
		{
			//chdir($dirname);
		}
		else
		{
			$ex = new MessageException(E_USER_ERROR, "The classpath folder {" . $amfbody->classPath . "} does not exist. You probably misplaced your service." , __FILE__, __LINE__, "AMFPHP_CLASSPATH_NOT_FOUND");
			MessageException::throwException($amfbody, $ex);
			return false;
		}
	   
		//$fileExists = @file_exists(basename($amfbody->classPath)); // see if the file exists
		$fileExists = @file_exists($amfbody->classPath); // see if the file exists
		if(!$fileExists)
		{
				$ex = new MessageException(E_USER_ERROR, "The class {" . $amfbody->className . "} could not be found under the class path {" . $amfbody->classPath . "}" , __FILE__, __LINE__, "AMFPHP_FILE_NOT_FOUND");
				MessageException::throwException($amfbody, $ex);
				return false;
		}
		
		global $amfphp;
		$time = microtime_float();
		//$fileIncluded = Executive::includeClass($amfbody, "./" . basename($amfbody->classPath));
		$fileIncluded = Executive::includeClass($amfbody, $amfbody->classPath);
		$amfphp['includeTime'] += microtime_float() - $time;
	
		if (!$fileIncluded) 
		{ 
			$ex = new MessageException(E_USER_ERROR, "The class file {" . $amfbody->className . "} exists but could not be included. The file may have syntax errors, or includes at the top of the file cannot be resolved.", __FILE__, __LINE__, "AMFPHP_FILE_NOT_INCLUDED");
			MessageException::throwException($amfbody, $ex);
			return false;
		}
		
		if (!class_exists($amfbody->className))
		{ // Just make sure the class name is the same as the file name
				
				$ex = new MessageException(E_USER_ERROR, "The file {" . $amfbody->className . ".php} exists and was included correctly but a class by that name could not be found in that file. Perhaps the class is misnamed.", __FILE__, __LINE__, "AMFPHP_CLASS_NOT_FOUND");
				MessageException::throwException($amfbody, $ex);
				return false;
		}

		//Let executive handle building the class
		//The executive can handle making exceptions and all that, that's why
		$classConstruct = Executive::buildClass($amfbody, $amfbody->className);
		initCakeController( $classConstruct );

		if($classConstruct !== '__amfphp_error')
		{
			$amfbody->setClassConstruct($classConstruct);
		}
		else
		{
			return false;
		}
	}
	return true;
}

function initCakeController( &$controller )
{
	$controller->autoRender = false;
	// DJH no idea how this works
	// DJH 9/29/2012 $controller->Component->init( $controller ); 
	$controller->constructClasses();
}
?>