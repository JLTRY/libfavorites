<?php

namespace JLTRY\LibFavorites;

\defined('_JEXEC') or die;

function array_remove_object(&$array, $value, $prop)
{
    return array_filter($array, function ($item) use ($value, $prop) {
        return $item->$prop !== $value;
    });
}

class HtmlItem
{
    protected $m_title = '';
    protected $m_type;
    protected $m_description = '';
    protected $m_bmkUri = null;
    protected $m_parent;
    protected $m_listchildren;
    public $m_id = -1;
    protected $m_modified = false;
    protected $m_dateAdded;
    protected $m_sortindex = 10;
    protected $m_parentName;
    protected $m_parentid;
    protected $m_hasDupe = false;
    protected $m_logger = null;

    public function __construct($parent, $params)
    {
        $this->m_listchildren = array();
        $this->m_parent = $parent;
        if ($parent !== null) {
            $this->m_parentName = $parent->gettitle();
            $this->m_parentid = $parent->getid();
            $parent->addChild($this);
        }
        foreach (get_class_vars(__CLASS__) as $attr => $value) {
            $field = substr($attr, 2);
            if (array_key_exists($field, $params)) {
                $this->$attr = $params[$field];
            }
        }
    }

    public function modify($params)
    {
        foreach (array('title' => 'm_title', 'description' => 'm_description', 'sortindex' => 'm_sortindex') as $key => $property) {
            if (array_key_exists($key, $params) && $this->$property != $params[$key]) {
                $this->$property = $params[$key];
                $this->m_modified = true;
            }
        }
    }

    public function delete()
    {
        if ($this->m_parent !== null) {
            $this->m_parent->removeChild($this);
        }
    }

    public function gettitle() { return $this->m_title; }
    public function getid() { return $this->m_id; }
    public function ismodified() { return $this->m_modified; }

    public function addChild($child)
    {
        $this->m_listchildren[] = $child;
        $child->setparent($this);
    }

    public function removeChild($child)
    {
        $this->m_listchildren = array_remove_object($this->m_listchildren, $child->getid(), 'm_id');
    }

    public function addChildren($children) { $this->m_listchildren = $children; }

    public function getids(&$map)
    {
        $map[$this->getid()] = $this;
        foreach ($this->m_listchildren as $child) { $child->getids($map); }
    }

    public function setparent($parent) { $this->m_parent = $parent; }
    public function getparent() { return $this->m_parent; }

    public function getpath()
    {
        return $this->m_parent ? $this->m_parent->getpath() . '/' . $this->gettitle() : $this->gettitle();
    }

    public function getchildren() { return $this->m_listchildren; }

    public function getlevel($level = 0)
    {
        if (count($this->m_listchildren) === 0) { return $level; }
        $deepest = $level;
        foreach ($this->m_listchildren as $child) { $deepest = max($child->getlevel($level), $deepest); }
        return $deepest + 1;
    }

    public function removeallchildren()
    {
        foreach ($this->m_listchildren as $child) {
            if ($child->getparent() === $this) { $child->setparent(null); }
        }
        $this->m_listchildren = array();
    }

    public function writelevel($level, &$result)
    {
        for ($i = 0; $i < $level; $i++) { $result .= "\t"; }
    }

    public function buildmapid1(&$map)
    {
        $map[$this->m_id] = $this;
        foreach ($this->m_listchildren as $child) { $child->buildmapid1($map); }
    }

    public function finditemnamed($title, &$array, $match = false, $immediate = 9999)
    {
        $found = false;
        if ($immediate >= 0 && ((!$match && $this->m_title == $title) || ($match && preg_match('/' . $title . '/', $this->m_title)))) {
            $array[] = $this;
            $found = true;
        }
        if (!$found) {
            foreach ($this->m_listchildren as $child) {
                if ($child->finditemnamed($title, $array, $match, $immediate - 1)) { $found = true; }
            }
        }
        return $found;
    }

    public function finditemwithid($id, &$array)
    {
        $found = false;
        if ($this->m_id == $id) { $array[] = $this; $found = true; }
        if (!$found) {
            foreach ($this->m_listchildren as $child) {
                if ($child->finditemwithid($id, $array)) { $found = true; }
            }
        }
        return $found;
    }

    public function finditemwithpath(&$path, &$array, $level = 9999)
    {
        if (!$path) { return false; }
        $matches = array();
        $found = $this->finditemnamed($path[0], $matches, false, $level);
        if (count($path) === 1) {
            foreach ($matches as $match) { $array[] = $match; }
        } elseif ($found) {
            array_shift($path);
            $found = false;
            foreach ($matches as $match) {
                if ($match->finditemwithpath($path, $array, 1)) { $found = true; }
            }
        }
        return $found;
    }

    public function finditemid($id, $level = 0)
    {
        if ($this->m_id == $id) { return $this; }
        if ($level > 50) { return null; }
        foreach ($this->m_listchildren as $child) {
            $found = $child->finditemid($id, $level + 1);
            if ($found !== null) { return $found; }
        }
        return null;
    }

    public function finditemwithtag($tag, &$array)
    {
        $found = false;
        if (property_exists($this, 'm_tags') && is_array($this->m_tags) && in_array($tag, $this->m_tags)) {
            $array[] = $this;
            $found = true;
        }
        foreach ($this->m_listchildren as $child) { if ($child->finditemwithtag($tag, $array)) { $found = true; } }
        return $found;
    }

    public function finditemwithkeyword($keyword, &$array)
    {
        $found = false;
        if (property_exists($this, 'm_keyword') && $keyword == $this->m_keyword) { $array[] = $this; $found = true; }
        foreach ($this->m_listchildren as $child) { if ($child->finditemwithkeyword($keyword, $array)) { $found = true; } }
        return $found;
    }

    public function getitemmodified(&$array)
    {
        $found = false;
        if ($this->ismodified()) { $array[] = $this; $found = true; }
        foreach ($this->m_listchildren as $child) { if ($child->getitemmodified($array)) { $found = true; } }
        return $found;
    }
}
