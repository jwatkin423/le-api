<?php
namespace Adrenalads\CommerceApi;

class Taxonomy
{

    protected $raw_categories;

    protected $categories;

    public function __construct($raw_categories)
    {
        $this->raw_categories = $raw_categories;
        $this->categories = array();
        $this->_buildLinkedList();
    }

    protected function _buildLinkedList()
    {
        foreach ($this->raw_categories as $category) {
            $this->_buildLinkedListForCategory($category);
        }
    }

    protected function _buildLinkedListForCategory($category, $parent = null)
    {
        $object = new Category([
            'id' => $category['id'],
            'title' => $category['title']
        ]);
        if ($parent) {
            $object->setParent($parent);
        }
        if (@count($category['children'])) {
            foreach ($category['children'] as $child) {
                $child = $this->_buildLinkedListForCategory($child, $object);
                $object->addChild($child);
            }
        }
        $this->categories[$category['id']] = $object;
        return $object;
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function getCategory($id)
    {
        return $this->categories[$id];
    }

    public function getRawCategories()
    {
        return $this->raw_categories;
    }

    public function getBreadcrumbs($id)
    {
        $breadcrumbs = [];
        @$current = $this->categories[$id];

        while (!is_null($current)) {
            $breadcrumbs[] = $current;
            $current = $current->getParent();
        }

        return array_reverse($breadcrumbs);
    }

    public function setCurrent($id)
    {
        @$active = $this->categories[$id];

        if ($active) {
            $active->makeCurrent();

            $parent = $active->getParent();
            while (!is_null($parent)) {
                $parent->makeExpanded();
                $parent = $parent->getParent();
            }
        }
    }

    public function breadcrumbsToArray($breadcrumb)
    {
        return [
            'title' => $breadcrumb->getTitle(),
            'url' => $breadcrumb->getCategoryURL()
        ];
    }
}
