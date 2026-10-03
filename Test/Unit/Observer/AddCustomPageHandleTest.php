<?php
declare(strict_types=1);

namespace Panth\NotFoundPage\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\LayoutInterface;
use Panth\NotFoundPage\Helper\Data;
use Panth\NotFoundPage\Observer\AddCustomPageHandle;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AddCustomPageHandleTest extends TestCase
{
    private function helper(bool $enabled): Data
    {
        $helper = $this->createMock(Data::class);
        $helper->method('isEnabled')->willReturn($enabled);

        return $helper;
    }

    private function observer(mixed $layout, ?string $fullActionName): Observer
    {
        $data = ['layout' => $layout];
        if ($fullActionName !== null) {
            $data['full_action_name'] = $fullActionName;
        }

        return new Observer(['event' => new Event($data)]);
    }

    private function layout(array $handles, bool $expectAdd): LayoutInterface
    {
        $update = $this->createMock(ProcessorInterface::class);
        $update->method('getHandles')->willReturn($handles);
        if ($expectAdd) {
            $update->expects($this->once())->method('addHandle')->with('panth_notfound_custom')->willReturnSelf();
        } else {
            $update->expects($this->never())->method('addHandle');
        }
        $layout = $this->createMock(LayoutInterface::class);
        $layout->method('getUpdate')->willReturn($update);

        return $layout;
    }

    public function testHandleConstantMatchesLayoutFile(): void
    {
        $this->assertSame('panth_notfound_custom', AddCustomPageHandle::HANDLE);
        $this->assertFileExists(
            __DIR__ . '/../../../view/frontend/layout/' . AddCustomPageHandle::HANDLE . '.xml'
        );
    }

    public function testAddsHandleForNoRouteActionWhenEnabled(): void
    {
        $observer = new AddCustomPageHandle($this->helper(true));
        $observer->execute($this->observer($this->layout(['default'], true), 'cms_noroute_index'));
    }

    public function testAddsHandleWhenNoRouteHandleIsAlreadyLoaded(): void
    {
        $observer = new AddCustomPageHandle($this->helper(true));
        $observer->execute(
            $this->observer($this->layout(['default', 'cms_noroute_index'], true), 'catalog_product_view')
        );
    }

    public function testAddsHandleWithoutFullActionNameWhenHandleIsPresent(): void
    {
        $observer = new AddCustomPageHandle($this->helper(true));
        $observer->execute($this->observer($this->layout(['cms_noroute_index'], true), null));
    }

    public function testSkipsWhenDisabled(): void
    {
        $observer = new AddCustomPageHandle($this->helper(false));
        $observer->execute($this->observer($this->layout(['cms_noroute_index'], false), 'cms_noroute_index'));
    }

    public function testSkipsOtherPages(): void
    {
        $helper = $this->createMock(Data::class);
        $helper->expects($this->never())->method('isEnabled');
        $observer = new AddCustomPageHandle($helper);
        $observer->execute(
            $this->observer($this->layout(['default', 'cms_index_index'], false), 'cms_index_index')
        );
        $observer->execute($this->observer($this->layout(['cms_page_view'], false), 'cms_page_view'));
    }

    public function testIgnoresEventWithoutLayout(): void
    {
        $helper = $this->createMock(Data::class);
        $helper->expects($this->never())->method('isEnabled');
        $observer = new AddCustomPageHandle($helper);
        $observer->execute($this->observer(null, 'cms_noroute_index'));
        $observer->execute($this->observer(new DataObject(), 'cms_noroute_index'));
    }
}
