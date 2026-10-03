<?php
declare(strict_types=1);

namespace Panth\NotFoundPage\Test\Unit\Block;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Panth\NotFoundPage\Block\NotFound;
use Panth\NotFoundPage\Helper\Data;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class NotFoundTest extends TestCase
{
    private Store $store;
    private UrlInterface $urlBuilder;

    protected function setUp(): void
    {
        $this->store = $this->createMock(Store::class);
        $this->store->method('getBaseUrl')->willReturn('https://shop.example.com/');
        $this->store->method('getId')->willReturn(2);
        $this->store->method('getRootCategoryId')->willReturn(7);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
    }

    private function block(Data $helper, ?CollectionFactory $factory = null): NotFound
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($this->store);
        $context = $this->createMock(Context::class);
        $context->method('getStoreManager')->willReturn($storeManager);
        $context->method('getUrlBuilder')->willReturn($this->urlBuilder);

        return new NotFound($context, $helper, $factory ?? $this->createMock(CollectionFactory::class));
    }

    private function category(?string $name, string $url): Category
    {
        $category = $this->createMock(Category::class);
        $category->method('getName')->willReturn($name);
        $category->method('getUrl')->willReturn($url);

        return $category;
    }

    public function testDelegatesSettingsToHelper(): void
    {
        $helper = $this->createMock(Data::class);
        $helper->method('isEnabled')->willReturn(true);
        $helper->method('getHeading')->willReturn('Nothing here');
        $helper->method('getSubheading')->willReturn('Try searching.');
        $helper->method('showSearch')->willReturn(false);
        $helper->method('showPopularLinks')->willReturn(true);
        $helper->method('showContactInfo')->willReturn(false);
        $helper->method('getContactEmail')->willReturn('help@example.com');
        $block = $this->block($helper);

        $this->assertTrue($block->isEnabled());
        $this->assertSame('Nothing here', $block->getHeading());
        $this->assertSame('Try searching.', $block->getSubheading());
        $this->assertFalse($block->showSearch());
        $this->assertTrue($block->showPopularLinks());
        $this->assertFalse($block->showContactInfo());
        $this->assertSame('help@example.com', $block->getContactEmail());
    }

    public function testHomeAndSearchUrls(): void
    {
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('catalogsearch/result', [])
            ->willReturn('https://shop.example.com/catalogsearch/result/');
        $block = $this->block($this->createMock(Data::class));

        $this->assertSame('https://shop.example.com/', $block->getHomeUrl());
        $this->assertSame('https://shop.example.com/catalogsearch/result/', $block->getSearchUrl());
    }

    public function testTopCategoriesQueryAndMapping(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())->method('setStoreId')->with(2)->willReturnSelf();
        $filters = [];
        $collection->method('addFieldToFilter')->willReturnCallback(
            function ($field, $condition) use (&$filters, $collection) {
                $filters[$field] = $condition;
                return $collection;
            }
        );
        $collection->expects($this->once())->method('addAttributeToSelect')
            ->with(['name', 'url_key'])->willReturnSelf();
        $collection->expects($this->once())->method('setOrder')->with('position', 'ASC')->willReturnSelf();
        $collection->expects($this->once())->method('setPageSize')->with(6)->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([
            $this->category(' Gear ', 'https://shop.example.com/gear.html'),
            $this->category('', 'https://shop.example.com/empty.html'),
            $this->category(null, 'https://shop.example.com/null.html'),
            $this->category('   ', 'https://shop.example.com/blank.html'),
            $this->category('Men & Boys', 'https://shop.example.com/men.html'),
        ]));
        $factory = $this->createMock(CollectionFactory::class);
        $factory->expects($this->once())->method('create')->willReturn($collection);

        $result = $this->block($this->createMock(Data::class), $factory)->getTopCategories();

        $this->assertSame(['parent_id' => 7, 'is_active' => 1, 'include_in_menu' => 1], $filters);
        $this->assertSame([
            ['name' => 'Gear', 'url' => 'https://shop.example.com/gear.html'],
            ['name' => 'Men & Boys', 'url' => 'https://shop.example.com/men.html'],
        ], $result);
    }

    public function testTopCategoriesEmptyCollection(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->method('setStoreId')->willReturnSelf();
        $collection->method('addFieldToFilter')->willReturnSelf();
        $collection->method('addAttributeToSelect')->willReturnSelf();
        $collection->method('setOrder')->willReturnSelf();
        $collection->method('setPageSize')->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([]));
        $factory = $this->createMock(CollectionFactory::class);
        $factory->method('create')->willReturn($collection);

        $this->assertSame([], $this->block($this->createMock(Data::class), $factory)->getTopCategories());
    }

    public function testTopCategoriesSwallowsErrors(): void
    {
        $factory = $this->createMock(CollectionFactory::class);
        $factory->method('create')->willThrowException(new \RuntimeException('database down'));

        $this->assertSame([], $this->block($this->createMock(Data::class), $factory)->getTopCategories());
    }
}
