<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Context\Context,
    Behat\Behat\Context\SnippetAcceptingContext,
    Behat\Behat\Exception\PendingException,
    Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Features context.
 */
class FeatureContext extends RawMinkContext implements Context, SnippetAcceptingContext {

    /**
     * Location to store screenshots, or false if none are to be taken
     * @var string|bool
     */
    protected $screenshot_dir = false;

    public function __construct($screenshot_dir=false) {
        if ( $screenshot_dir ) {
            $this->screenshot_dir = rtrim( $screenshot_dir, '/' ) . '/';
        }
    }

    /**
     * Wait for AJAX to finish.
     *
     * @Then /^I wait for AJAX to finish$/
     */
    public function iWaitForAjaxToFinish() {
        $this->getSession()->wait( 10000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))' );
    }

    /**
     * @AfterScenario
     */
    public function takeScreenshotAfterFailedStep(AfterScenarioScope $scope)
    {
        if ($this->screenshot_dir && TestResult::FAILED === $scope->getTestResult()->getResultCode()) {

            $feature  = $scope->getFeature();
            $scenario = $scope->getScenario();
            $filename = basename( $feature->getFile(), '.feature' ) . '-' . $scenario->getLine();

            if ($this->getSession()->getDriver() instanceof \Behat\Mink\Driver\Selenium2Driver) {
                $screenshot = $this->getSession()->getDriver()->getScreenshot();
                file_put_contents( $this->screenshot_dir . $filename . '.png', $screenshot);
            }

            //Store HTML markup of the page also - useful for non-js tests
            file_put_contents( $this->screenshot_dir . $filename . '.html', $this->getSession()->getPage()->getHtml());
        }
    }

}
