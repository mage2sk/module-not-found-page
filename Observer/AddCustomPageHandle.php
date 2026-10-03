<?php
declare(strict_types=1);

namespace Panth\NotFoundPage\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;
use Panth\NotFoundPage\Helper\Data;

class AddCustomPageHandle implements ObserverInterface
{
    public const HANDLE = 'panth_notfound_custom';
    private const NOROUTE_HANDLE = 'cms_noroute_index';

    public function __construct(
        private readonly Data $helper
    ) {
    }

    public function execute(Observer $observer): void
    {
        $layout = $observer->getEvent()->getData('layout');
        if (!$layout instanceof LayoutInterface) {
            return;
        }

        $update = $layout->getUpdate();
        $isNoRoute = $observer->getEvent()->getData('full_action_name') === self::NOROUTE_HANDLE
            || in_array(self::NOROUTE_HANDLE, $update->getHandles(), true);
        if (!$isNoRoute || !$this->helper->isEnabled()) {
            return;
        }

        $update->addHandle(self::HANDLE);
    }
}
