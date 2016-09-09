<?php
/* Generated on 6/26/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class UserIdentityCodeType extends EbatNs_FacetType
{
	const CodeType_eBayUser = 'eBayUser';
	const CodeType_eBayPartner = 'eBayPartner';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('UserIdentityCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_UserIdentityCodeType = new UserIdentityCodeType();
?>