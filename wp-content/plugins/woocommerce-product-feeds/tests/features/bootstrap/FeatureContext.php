<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
	/**
	 * Initializes context.
	 *
	 * Every scenario gets its own context instance.
	 * You can also pass arbitrary arguments to the
	 * context constructor through behat.yml.
	 */
	public function __construct()
	{
	}

	/**
	 * @When I click the element with CSS selector :selector
	 */
	public function iClickTheElementWithCssSelector( $selector ) {
		$element = $this->getSession()->getPage()->find( 'css', $selector );
		if ( empty( $element ) ) {
			throw new \Exception( sprintf( "The page '%s' does not contain the css selector '%s'", $this->getSession()->getCurrentUrl(), $selector ) );
		}
		$element->click();
	}

}
