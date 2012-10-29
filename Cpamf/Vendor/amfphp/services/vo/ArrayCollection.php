<?php
/**
 * ArrayCollection value object, for mapping array with flex ArrayCollection
 * class 
 *
 * @author Daniel Verner
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright (c) 2009 CarrotPlant Ltd.
 * @package cpamf
 * @subpackage 
 * @version $Id: ArrayCollection.php 12 2009-04-02 12:41:02Z daniel.verner $
 * 
 */

class ArrayCollection{
	public $_explicitType = "flex.messaging.io.ArrayCollection";
	public $source = array();
 
	function ArrayCollection( $source = array() ){
		$this->source = $source;
	}
}
  
?>