<?php
namespace Adrenalads\CommerceApi;

class Category
{
    protected $id;
    protected $title;
    protected $children;
    protected $parent;
    protected $current;
    protected $expanded;

    public function __construct($attributes = [])
    {
        $this->children = array();
        $this->current = false;
        $this->expanded = false;
        foreach ($attributes as $k => $v) {
            $this->$k = $v;
        }
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addChild($child)
    {
        array_push($this->children, $child);
    }

    public function getCategoryURL()
    {
        return route('category', $this->getSlug());
    }

    public function getSlug()
    {
        $current = $this;
        $slug = [];

        while (!is_null($current)) {
            $slug[] = $current->getID();
            $current = $current->getParent();
        }

        return array_reverse($slug);
    }

    public function getID()
    {
        return $this->id;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function makeCurrent()
    {
        $this->current = true;
    }

    public function makeExpanded()
    {
        $this->expanded = true;
    }

    public function cssClasses($overrides = [])
    {
        $overrides = array_merge([
            'expanded' => 'expanded',
            'current' => 'current'
        ], $overrides);

        $classes = [];
        if ($this->isExpanded()) {
            $classes[] = $overrides['expanded'];
        }
        if ($this->isCurrent()) {
            $classes[] = $overrides['current'];
        }

        return join($classes, ' ');
    }

    public function isExpanded()
    {
        return $this->expanded;
    }

    public function isCurrent()
    {
        return $this->current;
    }

}
