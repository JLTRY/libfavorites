<?php

namespace JLTRY\LibFavorites;

\defined('_JEXEC') or die;

class JsonFavorites
{
    private $log;
    private $file;
    private $jsoncontent;

    public function __construct($file, $log = false) { $this->log = $log; $this->file = $file; $this->jsoncontent = array(); }
    public function get_contents() { if (!file_exists($this->file)) { echo 'file does not exist' . $this->file; } if ($this->jsoncontent == array()) { $this->jsoncontent = json_decode(file_get_contents($this->file), true); } return $this->jsoncontent; }
    public function get_accountkeys() { return array(); }
    public function getinfo($type) { $this->get_contents(); return $type == 'collections' && array_key_exists('lastModified', $this->jsoncontent) ? (object) array('bookmarks' => $this->jsoncontent['lastModified']) : (object) array(); }
    public function getusername() { return ''; }
    public function getcollection($type) { $this->get_contents(); if ($type === 'bookmarks') { if (array_key_exists('children', $this->jsoncontent)) { return $this->jsoncontent['children'][1]; } if (array_key_exists('bookmarks', $this->jsoncontent)) { return $this->jsoncontent['bookmarks']['toolbar']; } return array(); } return null; }
    public static function converttofile($bookmark, $parent) { return new HtmlFile($parent, $bookmark); }
    public static function converttoitemlist($bookmark, $parent, $mapbookmark) { if (!array_key_exists('children', $bookmark)) { return; } foreach ($bookmark['children'] as $idchild) { if (!array_key_exists($idchild, $mapbookmark)) { continue; } $child = $mapbookmark[$idchild]; if (array_key_exists('children', $child)) { self::converttodirectory($child, $parent, $mapbookmark); } else { self::converttofile($child, $parent); } } }
    public static function converttodirectory($bookmark, $parent, $mapbookmark, $recurse = true) { $directory = new HtmlDirectory($parent, $bookmark); if ($recurse) { self::converttoitemlist($bookmark, $directory, $mapbookmark); } return $directory; }
    public static function converttoroot($jsonarray, $root) { $roots = array(); $map = array(); foreach ($jsonarray as $jsonbookmark) { $bookmark = get_object_vars($jsonbookmark); if (isset($bookmark['parentName']) && ($bookmark['parentName'] === 'root' || $bookmark['parentName'] === '')) { $roots[] = $bookmark; } if (isset($bookmark['id'])) { $map[$bookmark['id']] = $bookmark; } } foreach ($roots as $bookmark) { self::converttodirectory($bookmark, $root, $map); } }
    public static function convertjsontoitemlist($bookmark, $parent) { if (!array_key_exists('children', $bookmark)) { return; } foreach ($bookmark['children'] as $child) { if (array_key_exists('children', $child)) { self::convertjsontodirectory($child, $parent); } else { if (array_key_exists('uri', $child)) { $child['bmkUri'] = $child['uri']; } self::converttofile($child, $parent); } } }
    public static function convertjsontodirectory($bookmark, $parent, $recurse = true) { $directory = new HtmlDirectory($parent, $bookmark); if ($recurse) { self::convertjsontoitemlist($bookmark, $directory); } return $directory; }
    public static function convertjsontoroot($file) { $jsonfile = new self($file); $jsonfile->get_contents(); $root = new HtmlRoot('', -1, array()); self::convertjsontodirectory($jsonfile->getcollection('bookmarks'), $root); return $root; }
}
