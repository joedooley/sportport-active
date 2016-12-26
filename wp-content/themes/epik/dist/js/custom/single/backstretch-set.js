'use strict';

(function (document, $, undefined) {

	'use strict';

	var PrimaryBSHero = PrimaryBackstretchHero;
	var SecondaryBSHero = SecondaryBackstretchHero;

	$('.primary-hero').backstretch(PrimaryBSHero.primary_hero);
	$('.secondary-hero').backstretch(SecondaryBSHero.secondary_hero);
})(document, jQuery);