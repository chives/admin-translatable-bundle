<?php

namespace FSi\Bundle\AdminTranslatableBundle\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;

class AdminContext extends PageObjectContext implements KernelAwareInterface
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    function __construct()
    {
        $this->useContext('data', new DataContext());
        $this->useContext('TranslatableCRUD', new TranslatableCRUDContext());
    }

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given /^I am on the "([^"]*)" page$/
     */
    public function iAmOnThePage($pageName)
    {
        $this->getPage($pageName)->open();
    }

    /**
     * @Given /^I am on the "([^"]*)" page with translatable locale "([^"]*)"$/
     * @Given /^I am on the "([^"]*)" page with default translatable locale "([^"]*)"$/
     */
    public function iAmOnThePageWithTranslatableLocale($pageName, $locale)
    {
        $this->getPage($pageName)->open(array('locale' => $locale));
    }

    /**
     * @Given /^I am on the "([^"]*)" page with id (\d+)$/
     */
    public function iAmOnThePageWithId($pageName, $id)
    {
        $this->getPage($pageName)->open(array('id' => $id));
    }

    /**
     * @Given /^the following translatable locales were defined$/
     */
    public function theFollowingTranslatableLocalesWereDefined(TableNode $languages)
    {
        $definedLanguages = $this->kernel->getContainer()->getParameter('fsi_admin_translatable.locales');

        foreach ($languages as $language) {
            expect(in_array($language, $definedLanguages))->toBe(true);
        }
    }

    /**
     * @Then /^I should see translatable locale list$/
     */
    public function iShouldSeeTranslatableLocaleList()
    {
        expect($this->getElement('Top Menu')->hasTranslatableSwitcher())->toBe(true);
    }

    /**
     * @Given /^translatable locale list should have following locales$/
     */
    public function translatableSwitcherShouldHaveFollowingLocales(TableNode $locales)
    {
        foreach ($locales->getHash() as $locale) {
            expect($this->getElement('Top Menu')->hasFollowingLocales($locale['Locale']))->toBe(true);
        }
    }

    /**
     * @Given /^translatable locale list should be inactive$/
     */
    public function translatableLocaleListShouldBeInactive()
    {
        expect($this->getElement('Top Menu')->isTranslatableSwitcherActive())->toBe(false);
    }

    /**
     * @Given /^I choose "([^"]*)" from translatable locale list$/
     */
    public function iChooseLinkFromTranslatableLocaleList($translatableLocale)
    {
        $this->getElement('Top Menu')->clickTranslatableDropdown();
        $this->getElement('Top Menu')->findTranslatableLanguageElement($translatableLocale)->click();
    }

    /**
     * @Then /^I should see translatable list with "([^"]*)" option selected$/
     */
    public function iShouldSeeTranslatableListWithSelected($locale)
    {
        expect($this->getElement('Top Menu')->hasActiveTranslatableLanguage($locale))->toBe(true);
    }

    /**
     * @When /^I follow "([^"]*)" url from top bar$/
     * @Given /^I follow "([^"]*)" menu element$/
     */
    public function iFollowUrlFromTopBar($menuElement)
    {
        $this->getPage('Admin Panel')->getMenu()->clickLink($menuElement);
    }

    /**
     * @Given /^I should see "([^"]*)" page title "([^"]*)"$/
     */
    public function iShouldSeePageTitle($page, $title)
    {
        expect($this->getPage($page)->getTitle())->toBe($title);
    }

    /**
     * @Given /^the following admin translatable elements were registered$/
     * @Given /^the following admin non-translatable elements were registered$/
     * @Given /^the following non-translatable resources were registered$/
     * @Given /^the following translatable resources were registered$/
     */
    public function theFollowingAdminTranslatableElementsWereRegistered(TableNode $elements)
    {
        foreach ($elements->getHash() as $serviceRow) {
            expect($this->kernel->getContainer()->has($serviceRow['Service Id']))->toBe(true);
            expect($this->kernel->getContainer()->get($serviceRow['Service Id']))->toBeAnInstanceOf($serviceRow['Class']);
        }
    }

    /**
     * @Given /^I should see "([^"]*)" page header "([^"]*)"$/
     */
    public function iShouldSeePageHeader($pageName, $headerContent)
    {
        expect($this->getPage($pageName)->getTitle())->toBe($headerContent);
    }

    /**
     * @Given /^there are following resources added to resource map$/
     */
    public function thereAreFollowingResourcesAddedToResourceMap(TableNode $resources)
    {
        foreach ($resources->getHash() as $resource) {
            expect($this->kernel->getContainer()
                ->get('fsi_resource_repository.map_builder')
                ->hasResource($resource['Key']))->toBe(true);

            if (isset($resource['Type'])) {
                expect($this->kernel->getContainer()
                    ->get('fsi_resource_repository.map_builder')
                    ->getResource($resource['Key']))->toBeAnInstanceOf(
                        sprintf('FSi\Bundle\ResourceRepositoryBundle\Repository\Resource\Type\%sType', ucfirst($resource['Type']))
                    );
            }
        }
    }

    /**
     * @Given /^I fill form "([^"]*)" field with "([^"]*)"$/
     */
    public function iFillFormFieldWith($field, $value)
    {
        $this->getElement('Form')->fillField($field, $value);
    }

    /**
     * @Given /^I should see form "([^"]*)" field with value "([^"]*)"$/
     */
    public function iShouldSeeFormFieldWithValue($field, $value)
    {
        expect($this->getElement('Form')->findField($field)->getValue())->toBe($value);
    }

    /**
     * @Given /^I change first comment\'s text to "([^"]*)"$/
     */
    public function iChangeFirstCommentsTextTo($commentText)
    {
        $this->getElement('Form')->fillField('form_comments_0_text', $commentText);
    }

    /**
     * @Then /^I should see one comment with text "([^"]*)"$/
     */
    public function iShouldSeeOneCommentWithText($commentText)
    {
        expect($this->getElement('Form')->findField('form_comments_0_text')->getValue())->toBe($commentText);
    }
}
