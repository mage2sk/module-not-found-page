<?php
declare(strict_types=1);

namespace Panth\NotFoundPage\Block;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Panth\NotFoundPage\Helper\Data;

class NotFound extends Template
{
    private Data $helper;
    private CategoryCollectionFactory $categoryCollectionFactory;

    public function __construct(
        Context $context,
        Data $helper,
        CategoryCollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $data);
    }

    public function isEnabled(): bool { return $this->helper->isEnabled(); }
    public function getHeading(): string { return $this->helper->getHeading(); }
    public function getSubheading(): string { return $this->helper->getSubheading(); }
    public function showSearch(): bool { return $this->helper->showSearch(); }
    public function showPopularLinks(): bool { return $this->helper->showPopularLinks(); }
    public function showContactInfo(): bool { return $this->helper->showContactInfo(); }
    public function getContactEmail(): string { return $this->helper->getContactEmail(); }
    public function getHomeUrl(): string { return $this->_storeManager->getStore()->getBaseUrl(); }
    public function getSearchUrl(): string { return $this->getUrl('catalogsearch/result'); }

    /**
     * Get top-level store categories dynamically
     */
    public function getTopCategories(): array
    {
        try {
            $storeId = $this->_storeManager->getStore()->getId();
            $rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();

            $collection = $this->categoryCollectionFactory->create();
            $collection->setStoreId($storeId);
            $collection->addFieldToFilter('parent_id', $rootCategoryId);
            $collection->addFieldToFilter('is_active', 1);
            $collection->addFieldToFilter('include_in_menu', 1);
            $collection->addAttributeToSelect(['name', 'url_key']);
            $collection->setOrder('position', 'ASC');
            $collection->setPageSize(6);

            $categories = [];
            foreach ($collection as $cat) {
                $name = trim($cat->getName() ?? '');
                if (!$name) continue;
                $categories[] = [
                    'name' => $name,
                    'url' => $cat->getUrl(),
                ];
            }
            return $categories;
        } catch (\Exception $e) {
            return [];
        }
    }
}
