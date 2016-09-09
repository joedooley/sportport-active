<?php
/* Generated on 6/26/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class PromotionMethodCodeType extends EbatNs_FacetType
{
	const CodeType_CrossSell = 'CrossSell';
	const CodeType_UpSell = 'UpSell';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('PromotionMethodCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_PromotionMethodCodeType = new PromotionMethodCodeType();
?>