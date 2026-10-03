<?php
declare(strict_types=1);

namespace Panth\NotFoundPage\Test\Unit\View;

use Magento\Framework\Escaper;
use Panth\NotFoundPage\Block\NotFound;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class NotFoundTemplateTest extends TestCase
{
    private const TEMPLATE = __DIR__ . '/../../../view/frontend/templates/notfound.phtml';

    private function escaper(): Escaper
    {
        $escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $escaper = $this->createMock(Escaper::class);
        $escaper->method('escapeHtml')->willReturnCallback($escape);
        $escaper->method('escapeHtmlAttr')->willReturnCallback($escape);
        $escaper->method('escapeUrl')->willReturnCallback($escape);

        return $escaper;
    }

    private function block(array $overrides = []): NotFound
    {
        $values = array_merge([
            'isEnabled' => true,
            'getHeading' => 'Page Not Found',
            'getSubheading' => 'The page has moved.',
            'getHomeUrl' => 'https://shop.example.com/',
            'getSearchUrl' => 'https://shop.example.com/catalogsearch/result/',
            'showSearch' => true,
            'showPopularLinks' => true,
            'showContactInfo' => true,
            'getContactEmail' => 'help@example.com',
            'getTopCategories' => [
                ['name' => 'Gear', 'url' => 'https://shop.example.com/gear.html'],
                ['name' => 'Men & Boys', 'url' => 'https://shop.example.com/men.html'],
            ],
        ], $overrides);
        $block = $this->getMockBuilder(NotFound::class)
            ->disableOriginalConstructor()
            ->onlyMethods(array_keys($values))
            ->getMock();
        foreach ($values as $method => $value) {
            $block->method($method)->willReturn($value);
        }

        return $block;
    }

    private function render(NotFound $block): string
    {
        $escaper = $this->escaper();
        $renderer = static function (string $file) use ($block, $escaper): string {
            ob_start();
            include $file;
            return (string) ob_get_clean();
        };

        return $renderer(self::TEMPLATE);
    }

    private function xpath(string $html): \DOMXPath
    {
        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new \DOMXPath($dom);
    }

    public function testRendersNothingWhenDisabled(): void
    {
        $this->assertSame('', trim($this->render($this->block(['isEnabled' => false]))));
    }

    public function testFullPageStructureAndLandmarks(): void
    {
        $xpath = $this->xpath($this->render($this->block()));

        $this->assertSame(1, $xpath->query('//main[@id="maincontent"]')->length);
        $this->assertSame(1, $xpath->query('//main//*[@id="contentarea"][@tabindex="-1"]')->length);
        $this->assertSame('Page Not Found', trim($xpath->query('//h1')->item(0)->textContent));
        $this->assertSame(1, $xpath->query('//h1')->length);
        $this->assertSame(0, $xpath->query('//h3')->length);
        $this->assertSame('true', $xpath->query('//div[@class="panth-404-code"]')->item(0)->getAttribute('aria-hidden'));
        $this->assertSame('The page has moved.', trim($xpath->query('//p[@class="panth-404-sub"]')->item(0)->textContent));
        foreach ($xpath->query('//svg') as $svg) {
            $this->assertSame('true', $svg->getAttribute('aria-hidden'));
            $this->assertSame('false', $svg->getAttribute('focusable'));
        }
    }

    public function testSearchFormIsAccessibleAndTargetsCatalogSearch(): void
    {
        $xpath = $this->xpath($this->render($this->block()));
        $form = $xpath->query('//form[@role="search"]')->item(0);

        $this->assertNotNull($form);
        $this->assertSame('https://shop.example.com/catalogsearch/result/', $form->getAttribute('action'));
        $this->assertSame('get', $form->getAttribute('method'));
        $input = $xpath->query('.//input[@name="q"]', $form)->item(0);
        $this->assertNotSame('', $input->getAttribute('aria-label'));
        $this->assertTrue($input->hasAttribute('required'));
        $this->assertSame('128', $input->getAttribute('maxlength'));
        $this->assertSame(1, $xpath->query('.//button[@type="submit"]', $form)->length);
    }

    public function testNavigationButtons(): void
    {
        $xpath = $this->xpath($this->render($this->block()));
        $home = $xpath->query('//a[contains(@class,"panth-404-btn-p")]')->item(0);
        $back = $xpath->query('//button[contains(@class,"panth-404-btn-s")]')->item(0);

        $this->assertSame('https://shop.example.com/', $home->getAttribute('href'));
        $this->assertSame('Back to Homepage', $home->getAttribute('aria-label'));
        $this->assertSame('button', $back->getAttribute('type'));
        $this->assertSame('Go Back', $back->getAttribute('aria-label'));
        $this->assertSame('https://shop.example.com/', $back->getAttribute('data-home-url'));
        $this->assertStringContainsString('history.length > 1', $back->getAttribute('onclick'));
    }

    public function testCategoryLinksAreEscapedInsideLabelledNav(): void
    {
        $html = $this->render($this->block());
        $xpath = $this->xpath($html);
        $nav = $xpath->query('//nav[@aria-labelledby="panth-404-cats-title"]')->item(0);

        $this->assertNotNull($nav);
        $this->assertSame('Browse Categories', trim($xpath->query('.//h2[@id="panth-404-cats-title"]', $nav)->item(0)->textContent));
        $links = $xpath->query('.//a[@class="panth-404-cat"]', $nav);
        $this->assertSame(2, $links->length);
        $this->assertSame('Men & Boys', $links->item(1)->textContent);
        $this->assertStringContainsString('Men &amp; Boys', $html);
    }

    public function testContactLine(): void
    {
        $xpath = $this->xpath($this->render($this->block()));
        $this->assertSame('mailto:help@example.com', $xpath->query('//div[@class="panth-404-help"]/a')->item(0)->getAttribute('href'));

        $hidden = $this->xpath($this->render($this->block(['getContactEmail' => ''])));
        $this->assertSame(0, $hidden->query('//div[@class="panth-404-help"]')->length);

        $off = $this->xpath($this->render($this->block(['showContactInfo' => false])));
        $this->assertSame(0, $off->query('//div[@class="panth-404-help"]')->length);
    }

    public function testOptionalSectionsCanBeSwitchedOff(): void
    {
        $xpath = $this->xpath($this->render($this->block([
            'showSearch' => false,
            'showPopularLinks' => false,
            'getSubheading' => '',
        ])));

        $this->assertSame(0, $xpath->query('//form')->length);
        $this->assertSame(0, $xpath->query('//nav')->length);
        $this->assertSame(0, $xpath->query('//p[@class="panth-404-sub"]')->length);
        $this->assertSame(1, $xpath->query('//a[contains(@class,"panth-404-btn-p")]')->length);
    }

    public function testCategoriesSectionHiddenWhenNoCategories(): void
    {
        $xpath = $this->xpath($this->render($this->block(['getTopCategories' => []])));
        $this->assertSame(0, $xpath->query('//nav')->length);
    }

    public function testUserContentIsEscaped(): void
    {
        $html = $this->render($this->block([
            'getHeading' => '<script>alert(1)</script>',
            'getSubheading' => '"><img src=x onerror=alert(1)>',
        ]));

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testMobileStylesKeepReadableTextAndTapTargets(): void
    {
        $css = (string) file_get_contents(self::TEMPLATE);

        $this->assertMatchesRegularExpression('/\.panth-404-btn \{[^}]*min-height: 44px/s', $css);
        $this->assertMatchesRegularExpression(
            '/@media \(max-width: 1024px\), \(pointer: coarse\) \{\s*\.panth-404-cat \{ min-height: 44px/s',
            $css
        );
        $this->assertMatchesRegularExpression('/@media \(max-width: 767px\)[^@]*\.panth-404-sub \{ font-size: 14px; \}/s', $css);
        $this->assertMatchesRegularExpression('/@media \(max-width: 1024px\)[^@]*font-size: 16px !important/s', $css);
        $this->assertStringNotContainsString('#A3A3A3', $css);
        $this->assertStringContainsString(':focus-visible', $css);
    }
}
