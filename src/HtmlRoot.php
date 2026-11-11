<?php

namespace JLTRY\LibFavorites;

\defined('_JEXEC') or die;

require_once __DIR__ . '/HtmlDirectory.php';

class HtmlRoot extends HtmlDirectory
{
    protected $m_user;
    protected $m_lastmodification;
    protected $m_map;
    protected $m_accountkeys;

    public function __construct($user, $lastmodification, $accountkeys = array(), $title = 'root', $url = '')
    {
        $this->m_user = $user;
        $this->m_lastmodification = $lastmodification;
        $this->m_map = array();
        $this->m_accountkeys = $accountkeys;
        parent::__construct(null, array('type' => 'root', 'title' => $title, 'bmkUri' => $url));
    }

    public function getaccountkeys() { return $this->m_accountkeys; }
    public function setaccountkeys($ar) { $this->m_accountkeys = $ar; }

    public function __sleep()
    {
        return array('m_user', 'm_lastmodification', 'm_title', 'm_description', 'm_parent', 'm_listchildren', 'm_id', 'm_modified', 'm_sortindex', 'm_accountkeys');
    }

    public function getlastmodification() { return $this->m_lastmodification; }
    public function setlastmodification($last) { $this->m_lastmodification = $last; }
    public static function load($file) { return unserialize(file_get_contents($file)); }
    public function save($serfile) { file_put_contents($serfile, serialize($this)); }
    public function savejson($serfile) { file_put_contents($serfile, json_encode($this)); }
}
