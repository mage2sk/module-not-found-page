<?php
declare(strict_types=1);

namespace Panth\NotFoundPage\Test\Unit\Config;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\Dom;
use Panth\NotFoundPage\Block\NotFound;
use Panth\NotFoundPage\Observer\AddCustomPageHandle;
use PHPUnit\Framework\TestCase;

class ModuleConfigurationTest extends TestCase
{
    private const MODULE = 'Panth_NotFoundPage';

    private string $moduleDir;

    protected function setUp(): void
    {
        $path = (new ComponentRegistrar())->getPath(ComponentRegistrar::MODULE, self::MODULE);
        $this->assertNotNull($path, 'Panth_NotFoundPage is not registered');
        $this->moduleDir = realpath($path);
    }

    private function load(string $relative, ?string $schemaUrn = null): \DOMXPath
    {
        $file = $this->moduleDir . '/' . $relative;
        $this->assertFileExists($file);
        $dom = new \DOMDocument();
        $this->assertTrue($dom->load($file), $relative . ' is not well formed');
        if ($schemaUrn !== null) {
            $errors = Dom::validateDomDocument($dom, $schemaUrn);
            $this->assertSame([], array_map('strval', $errors), $relative . ' violates ' . $schemaUrn);
        }

        return new \DOMXPath($dom);
    }

    private function values(\DOMXPath $xpath, string $query): array
    {
        $result = [];
        foreach ($xpath->query($query) as $node) {
            $result[] = trim($node->nodeValue);
        }

        return $result;
    }

    public function testRegistrationPointsToModuleRoot(): void
    {
        $this->assertSame(realpath(dirname(__DIR__, 3)), $this->moduleDir);
    }

    public function testModuleSequence(): void
    {
        $xpath = $this->load('etc/module.xml', 'urn:magento:framework:Module/etc/module.xsd');
        $this->assertSame(
            ['Panth_Core', 'Magento_Cms', 'Magento_Store', 'Magento_Catalog'],
            $this->values($xpath, '//module[@name="Panth_NotFoundPage"]/sequence/module/@name')
        );
    }

    public function testObserverIsRegisteredOnFrontendLayoutLoad(): void
    {
        $xpath = $this->load('etc/frontend/events.xml', 'urn:magento:framework:Event/etc/events.xsd');
        $this->assertSame(
            [AddCustomPageHandle::class],
            $this->values($xpath, '//event[@name="layout_load_before"]/observer/@instance')
        );
        $this->assertFileDoesNotExist($this->moduleDir . '/etc/events.xml');
    }

    public function testDefaults(): void
    {
        $xpath = $this->load('etc/config.xml');
        $base = '/config/default/panth_notfound/';
        $this->assertSame(['1'], $this->values($xpath, $base . 'general/enabled'));
        $this->assertSame(['Page Not Found'], $this->values($xpath, $base . 'content/heading'));
        $this->assertNotSame([''], $this->values($xpath, $base . 'content/subheading'));
        $this->assertSame(['1'], $this->values($xpath, $base . 'content/show_search'));
        $this->assertSame(['1'], $this->values($xpath, $base . 'content/show_popular_links'));
        $this->assertSame(['1'], $this->values($xpath, $base . 'content/show_contact_info'));
        $this->assertSame([''], $this->values($xpath, $base . 'content/contact_email'));
    }

    public function testAdminFieldsAreStoreScopedWithValidation(): void
    {
        $xpath = $this->load('etc/adminhtml/system.xml');
        $section = '//section[@id="panth_notfound"]';
        $this->assertSame(['Magento_Config::config'], $this->values($xpath, $section . '/resource'));
        $this->assertSame(['enabled'], $this->values($xpath, $section . '/group[@id="general"]/field/@id'));
        $this->assertSame(
            ['heading', 'subheading', 'show_search', 'show_popular_links', 'show_contact_info', 'contact_email'],
            $this->values($xpath, $section . '/group[@id="content"]/field/@id')
        );
        foreach ($xpath->query($section . '/group/field') as $field) {
            foreach (['showInDefault', 'showInWebsite', 'showInStore'] as $scope) {
                $this->assertSame('1', $field->getAttribute($scope), $field->getAttribute('id') . ' ' . $scope);
            }
        }
        $email = $section . '/group[@id="content"]/field[@id="contact_email"]';
        $this->assertSame(['validate-email'], $this->values($xpath, $email . '/validate'));
        $this->assertSame(['1'], $this->values($xpath, $email . '/depends/field[@id="show_contact_info"]'));
        $this->assertSame(
            array_fill(0, 4, 'Magento\Config\Model\Config\Source\Yesno'),
            $this->values($xpath, $section . '/group/field[@type="select"]/source_model')
        );
    }

    public function testCustomLayoutHandle(): void
    {
        $xpath = $this->load(
            'view/frontend/layout/' . AddCustomPageHandle::HANDLE . '.xml',
            'urn:magento:framework:View/Layout/etc/page_configuration.xsd'
        );
        $this->assertSame(['404 - Page Not Found'], $this->values($xpath, '/page/head/title'));
        $this->assertSame(['NOINDEX,FOLLOW'], $this->values($xpath, '/page/head/meta[@name="robots"]/@content'));
        $this->assertSame(
            ['cms_page', 'page.main.title'],
            $this->values($xpath, '//referenceBlock[@remove="true"]/@name')
        );
        $this->assertSame(['main.content'], $this->values($xpath, '//referenceContainer[@remove="true"]/@name'));
        $block = $xpath->query('//referenceContainer[@name="page.wrapper"]/block')->item(0);
        $this->assertSame(NotFound::class, $block->getAttribute('class'));
        $this->assertSame('Panth_NotFoundPage::notfound.phtml', $block->getAttribute('template'));
        $this->assertFileExists($this->moduleDir . '/view/frontend/templates/notfound.phtml');
    }

    public function testComposerMetadata(): void
    {
        $composer = json_decode((string) file_get_contents($this->moduleDir . '/composer.json'), true);
        $this->assertSame('mage2kishan/module-not-found-page', $composer['name']);
        $this->assertSame('magento2-module', $composer['type']);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $composer['version']);
        $this->assertSame('Panth\\NotFoundPage\\', array_key_first($composer['autoload']['psr-4']));
    }
}
