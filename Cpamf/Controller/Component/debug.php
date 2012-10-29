<?php
/**
 * Write variables to file(debugging)
 *
 * @author Daniel Verner
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright (c) 2009 CarrotPlant Ltd.
 * @package cpamf
 * @subpackage 
 * @version $Id: debug.php 12 2009-04-02 12:41:02Z daniel.verner $
 * 
 */

class DebugComponent extends Object 
{
	function writeFile( $var, $fileName = "debug.txt", $appendFlag = false )
	{
		ob_start();
		print_r( $var );
		$output = ob_get_clean();
		file_put_contents( $fileName, 
						   $output,
						   $appendFlag ? FILE_APPEND : 0 );		
	}
}



?>