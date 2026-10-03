<?php
declare(strict_types=1);

namespace Panth\NotFoundPage\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Panth\NotFoundPage\Helper\Data;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class DataTest extends TestCase
{
    private function helper(array $values): Data
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(
            function (string $path, string $scope = 'default') use ($values) {
                $this->assertSame(ScopeInterface::SCOPE_STORE, $scope, $path . ' must be read at store scope');
                return $values[$path] ?? null;
            }
        );
        $context = $this->createMock(Context::class);
        $context->method('getScopeConfig')->willReturn($scopeConfig);

        return new Data($context);
    }

    public static function flagProvider(): array
    {
        return [
            'enabled' => ['isEnabled', 'panth_notfound/general/enabled'],
            'search' => ['showSearch', 'panth_notfound/content/show_search'],
            'popular links' => ['showPopularLinks', 'panth_notfound/content/show_popular_links'],
            'contact info' => ['showContactInfo', 'panth_notfound/content/show_contact_info'],
        ];
    }

    #[DataProvider('flagProvider')]
    public function testFlagsFollowStoreConfig(string $method, string $path): void
    {
        $this->assertTrue($this->helper([$path => '1'])->$method());
        $this->assertFalse($this->helper([$path => '0'])->$method());
        $this->assertFalse($this->helper([])->$method());
    }

    public function testHeadingFallsBackToDefaultWhenEmpty(): void
    {
        $this->assertSame('Page Not Found', $this->helper([])->getHeading());
        $this->assertSame('Page Not Found', $this->helper(['panth_notfound/content/heading' => ''])->getHeading());
        $this->assertSame(
            'Lost in the aisles',
            $this->helper(['panth_notfound/content/heading' => 'Lost in the aisles'])->getHeading()
        );
    }

    public function testSubheadingIsEmptyStringWhenNotConfigured(): void
    {
        $this->assertSame('', $this->helper([])->getSubheading());
        $this->assertSame(
            'Try the search below.',
            $this->helper(['panth_notfound/content/subheading' => 'Try the search below.'])->getSubheading()
        );
    }

    public static function emailProvider(): array
    {
        return [
            'not set' => [null, ''],
            'empty' => ['', ''],
            'whitespace only' => ['   ', ''],
            'legacy placeholder' => ['support@example.com', ''],
            'legacy placeholder upper case' => ['  SUPPORT@EXAMPLE.COM ', ''],
            'invalid address' => ['not-an-email', ''],
            'missing domain' => ['help@', ''],
            'valid' => ['help@shop.example.com', 'help@shop.example.com'],
            'valid trimmed' => ["  care@example.org\n", 'care@example.org'],
        ];
    }

    #[DataProvider('emailProvider')]
    public function testContactEmailIsSanitised(?string $stored, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->helper(['panth_notfound/content/contact_email' => $stored])->getContactEmail()
        );
    }
}
