<?php
/**
 * Extended version of the MethodTable class, for use with cakePHP controllers 
 *
 * @author Daniel Verner
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright (c) 2009 CarrotPlant Ltd.
 * @package cpamf
 * @subpackage 
 * @version $Id: CakeMethodTable.php 12 2009-04-02 12:41:02Z daniel.verner $
 * 
 */
include_once(AMFPHP_BASE . "shared/util/MethodTable.php");

class CakeMethodTable extends MethodTable
{
	/**
	 * Constructor.
	 *
	 * Since this class should only be accessed through the static create() method
	 * this constructor should be made private. Unfortunately, this is not possible
	 * in PHP4.
	 *
	 * @access private
	 */
	function CakeMethodTable(){
	}


	/**
	 * Creates the methodTable for a passed class.
	 *
	 * @static
	 * @access public
	 * @param $className(String) The name of the service class.
	 *        May also simply be __FILE__
	 * @param $servicePath(String) The location of the classes (optional)
	 */
	function create($className, $servicePath = NULL, &$classComment){
		
		$methodTable = array();
		if( file_exists(Inflector::underscore( $className ) ) )
		{
			//The new __FILE__ way of doing things was used
			// Files are underscored in cakePHP
			$sourcePath = Inflector::underscore( $className ) ;
			$className = str_replace("\\", '/', $className);
			$className = substr($className, strrpos($className, '/') + 1);
			// Class names are CamelCased in cakePHP 
			$className = Inflector::camelize( str_replace('.php', '', $className) );
		}
		else
		{
			$className = str_replace('.php', '', $className);
			$fullPath = Inflector::underscore( str_replace('.', '/', $className) );
			$className = Inflector::camelize( $fullPath );
			if(strpos($fullPath, '/') !== FALSE)
			{
				// Class names are CamelCased in cakePHP 				
				$className = Inflector::camelize( substr(strrchr($fullPath, '/'), 1) );
			}
			
			if($servicePath == NULL)
			{
				if(isset($GLOBALS['amfphp']['classPath']))
				{
					$servicePath = $GLOBALS['amfphp']['classPath'];
				}
				else
				{
					$servicePath = "../services/";
				}
			}
			$sourcePath = $servicePath . $fullPath . ".php";
		}
		
		if(!file_exists($sourcePath))
		{
			trigger_error("The MethodTable class could not find {" . 
				$sourcePath . "}", 
				E_USER_ERROR);
		}
		
		if(class_exists('ReflectionClass'))
		{
			//PHP5
			$classMethods = MethodTable::getClassMethodsReflection($sourcePath, $className, $classComment);
		}
		else
		{
			//PHP4
			
			$classMethods = MethodTable::getClassMethodsTokenizer($sourcePath, $className, $classComment);
		}
		
		foreach ($classMethods as $key => $value) {
			if($value['name'][0] == '_' || $value['name'] == 'beforeFilter')
			{
				continue;
			}
			$methodSignature = $value['args'];
			$methodName = $value['name'];
			$methodComment = $value['comment'];
			
			$description = MethodTable::getMethodDescription($methodComment) . " " . MethodTable::getMethodCommentAttribute($methodComment, "desc");
			$description = trim($description);
			$access = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "access");
			$roles = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "roles");
			$instance = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "instance");
			$returns = MethodTable::getMethodCommentAttributeFirstLine($methodComment, "returns");
			$pagesize = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "pagesize");
			$params = MethodTable::getMethodCommentArguments($methodComment);
						
			//description, arguments, access, [roles, [instance, [returns, [pagesize]]]]
			$methodTable[$methodName] = array();
			//$methodTable[$methodName]["signature"] = $methodSignature; //debug purposes
			$methodTable[$methodName]["description"] = ($description == "") ? "No description given." : $description;
			$methodTable[$methodName]["arguments"] = MethodTable::getMethodArguments($methodSignature, $params);
			$methodTable[$methodName]["access"] = ($access == "") ? "private" : $access;
			
			if($roles != "") $methodTable[$methodName]["roles"] = $roles;
			if($instance != "") $methodTable[$methodName]["instance"] = $instance;
			if($returns != "") $methodTable[$methodName]["returns"] = $returns;
			if($pagesize != "") $methodTable[$methodName]["pagesize"] = $pagesize;
		}
		
		$classComment = trim(str_replace("\r\n", "\n", MethodTable::getMethodDescription($classComment)));
		
		return $methodTable;
	}
}
?>