<?php
/* Generated on 6/26/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class UUIDType extends EbatNs_FacetType
{

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('UUIDType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_UUIDType = new UUIDType();
?>