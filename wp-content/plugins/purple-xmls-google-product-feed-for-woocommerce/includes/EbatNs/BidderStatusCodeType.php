<?php
/* Generated on 6/26/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class BidderStatusCodeType extends EbatNs_FacetType
{
	const CodeType_Approved = 'Approved';
	const CodeType_Denied = 'Denied';
	const CodeType_Pending = 'Pending';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('BidderStatusCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_BidderStatusCodeType = new BidderStatusCodeType();
?>