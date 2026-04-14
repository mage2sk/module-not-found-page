<?php
declare(strict_types=1);

namespace Panth\NotFoundPage\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('panth_notfound/general/enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getHeading(): string
    {
        return (string)$this->scopeConfig->getValue('panth_notfound/content/heading', ScopeInterface::SCOPE_STORE) ?: 'Page Not Found';
    }

    public function getSubheading(): string
    {
        return (string)$this->scopeConfig->getValue('panth_notfound/content/subheading', ScopeInterface::SCOPE_STORE) ?: '';
    }

    public function showSearch(): bool
    {
        return (bool)$this->scopeConfig->getValue('panth_notfound/content/show_search', ScopeInterface::SCOPE_STORE);
    }

    public function showPopularLinks(): bool
    {
        return (bool)$this->scopeConfig->getValue('panth_notfound/content/show_popular_links', ScopeInterface::SCOPE_STORE);
    }

    public function showContactInfo(): bool
    {
        return (bool)$this->scopeConfig->getValue('panth_notfound/content/show_contact_info', ScopeInterface::SCOPE_STORE);
    }

    public function getContactEmail(): string
    {
        return (string)$this->scopeConfig->getValue('panth_notfound/content/contact_email', ScopeInterface::SCOPE_STORE) ?: '';
    }
}
