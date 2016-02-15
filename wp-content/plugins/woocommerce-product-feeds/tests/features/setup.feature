Feature: Set up plugin settings
    In order for a the site to generate a feed
    As a website administrator
    I need to be able to access the plugin settings

Scenario: I want to log in
    Given I go to "/wp-login.php"
    And I fill in "log" with "testadmin"
    And I fill in "pwd" with "testadmin"
	When I press "Log In"
	Then the url should match "/wp-admin/"
    And I should see "Dashboard"
    When I click the element with CSS selector ".toplevel_page_woocommerce"
    And I should see "Orders"
    When I follow "Settings"
    Then I should see "General Options"
    When I follow "Product Feeds"
    Then print current URL
    And I should see "Notes about Google"