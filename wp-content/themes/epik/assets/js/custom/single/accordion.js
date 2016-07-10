(function ($, window, document, undefined) {

	// http://codepen.io/joshhunt18/pen/JLtFq

	'use strict';

	// Grab the accordion items from the page
	var accordionItems   = document.querySelectorAll('.accordion-item'),
	    accordionContent = document.querySelectorAll('.accordion-content');

	// Assign onclick events to the accordion item headings
	for (var i = 0; i < accordionItems.length; i++) {
		var h2     = getFirstChildWithTagName(accordionItems[i], 'H2');
		h2.onclick = toggleItem;
	}

	// Hide all accordion item bodies except the first
	for (var i = 1; i < accordionItems.length; i++) {
		accordionItems[i].className = 'accordion-item closed';
	}

	// Add exact height to div that is shown on page load
	var firstDiv          = accordionItems[0].getElementsByTagName('div')[0];
	firstDiv.style.height = "auto";
	var firstDivHeight    = firstDiv.offsetHeight;
	firstDiv.style.height = firstDivHeight + 'px';

	function toggleItem() {
		var itemClass = this.parentNode.className;

		// Hide all items
		for (var i = 0; i < accordionItems.length; i++) {
			accordionItems[i].className                                   = 'accordion-item closed';
			accordionItems[i].getElementsByTagName('div')[0].style.height = "0";
		}

		// Show this item if it was previously hidden
		if (itemClass == 'accordion-item closed') {
			var accordionContent     = accordionItemContent,
			    accordionItemContent = this;

			do accordionItemContent = accordionItemContent.nextSibling; while (accordionItemContent && accordionItemContent.nodeType !== 1);

			accordionItemContent.style.height = "auto";
			var divHeight                     = accordionItemContent.offsetHeight;
			accordionItemContent.style.height = "";
			this.parentNode.className         = 'accordion-item';
			var that                          = this;
			window.setTimeout(function () {
				accordionItemContent.style.height = divHeight + 'px';
			}, 50);
		}
	}

	// Helper function
	function getFirstChildWithTagName(element, tagName) {
		for (var i = 0; i < element.childNodes.length; i++) {
			if (element.childNodes[i].nodeName == tagName) return element.childNodes[i];
		}
	}

	// @TODO
	// From FAQ-Plugin in Tonya's class
	//function closeAnswersOnPageLoad() {
	//
	//	$.each($questions, function (index, element) {
	//		var $question = $(element);
	//
	//		if (isAnswerToBeClosed($question)) {
	//			var $answer = getAnswer(index);
	//
	//			$answer.hide();
	//		}
	//	});
	//}

	//
	// var accHD = document.getElementsByClassName('accordion-heading'),
	// 	accItem = document.getElementsByClassName('accordion-content'),
	// 	i,
	// 	accHeadings = accHD.length,
	// 	heading = accHD[i];
	//
	// // console.log(accHD);
	// // console.log(accItem);
	//
	// for (i = 0; i < accHeadings; i++) {
	// 	// console.log(accHD[i]);
	// 	accHD[i].addEventListener('click', toggleItem, false                                                                                                                    );
	// 	console.log(accHD[i]);
	//
	// }
	//
	// function toggleItem() {
	// 	var itemClass = this.parentNode.className;
	//
	// 	// Hide all items
	// 	for (var i = 0; i < accordionItems.length; i++) {
	// 		accordionItems[i].className                                   = 'accordion-item closed';
	// 		accordionItems[i].getElementsByTagName('div')[0].style.height = "0";
	// 	}
	//
	// 	// Show this item if it was previously hidden
	// 	if (itemClass == 'accordion-item closed') {
	// 		var accordionContent     = accordionItemContent,
	// 		    accordionItemContent = this;
	//
	// 		do accordionItemContent = accordionItemContent.nextSibling; while (accordionItemContent && accordionItemContent.nodeType !== 1);
	//
	// 		accordionItemContent.style.height = "auto";
	// 		var divHeight                     = accordionItemContent.offsetHeight;
	// 		accordionItemContent.style.height = "";
	// 		this.parentNode.className         = 'accordion-item';
	// 		var that                          = this;
	// 		window.setTimeout(function () {
	// 			accordionItemContent.style.height = divHeight + 'px';
	// 		}, 50);
	// 	}
	// }


	// function toggleItem() {
	// 	//console.log(this);
	// 	var itemClass = this.parentNode.className;
	// 	//console.log(itemClass);
	//
	// 	for (i = 0; i < accItem.length; i++) {
	// 		accItem[i].className = 'accordion-item closed';
	// 	}
	// 	if (itemClass == 'accordion-item closed') {
	// 		this.parentNode.className = 'accordion-item open';
	// 	}
	// }

})(jQuery, window, document);
