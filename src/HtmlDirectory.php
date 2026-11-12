<?php

namespace JLTRY\LibFavorites;

\defined('_JEXEC') or die;

class HtmlDirectory extends HtmlItem
{
    public function __construct($parent, $params) { parent::__construct($parent, $params); }
    public function modify($params) { parent::modify($params); }
    public function gettype() { return 'directory'; }

    public function callmethodjson($method, $args)
    {
        $children = array();
        foreach ($this->m_listchildren as $child) {
            $argsi = $args;
            $argsi['encode'] = false;
            $res = call_user_func_array(array($child, $method), $argsi);
            if (is_array($res)) { $children[] = $res; }
        }
        $fields = count($children) ? array('id' => $this->m_id, 'title' => $this->m_title, 'children' => $children) : true;
        return $args['encode'] ? json_encode($fields) : $fields;
    }

    public function geturi($check = false, $encode = true)
    {
        if (!$check) { return '#'; }
        return $this->callmethodjson('geturi', array('check' => $check, 'encode' => $encode));
    }

    public function writehtml($level, &$result, $short = false, $maxlevel = 9999, $showkeyword = false, $type = 'all')
    {
        if ($level > 0 && $level < $maxlevel) {
            $result .= '<LI>';
            if ($this->m_title !== '') { self::writelevel($level, $result); $result .= '<H4>' . $this->m_title . '</H4>'; }
            if ($this->m_description !== '' && !$short) { self::writelevel($level, $result); $result .= '<DD>' . $this->m_description . "\n"; }
        }
        if ($level < $maxlevel) {
            if (count($this->m_listchildren)) { self::writelevel($level, $result); $result .= "<UL>\n"; }
            foreach ($this->m_listchildren as $child) { $child->writehtml($level + 1, $result, $short, $maxlevel, $showkeyword, $type); }
            if (count($this->m_listchildren)) { self::writelevel($level, $result); $result .= "</UL>\n"; }
        }
    }

    public function writecss3treeview($level, &$result, $short = false, $maxlevel = 9999, $showkeyword = false, $openlevel = 9999, $minlevel = 1, $type = 'all')
    {
        if ($level >= $minlevel && $level < $maxlevel) {
            self::writelevel($level, $result);
            $result .= '<LI>';
            if ($this->getlevel() >= 1 && $level < $maxlevel - 1) {
                $result .= $level < $openlevel ? '<input type="checkbox" checked="checked" >' : '<input type="checkbox" >';
            } else { $result .= '<input type="radio" >'; }
            if ($this->m_title !== '') {
                $result .= $this->m_bmkUri !== null ? '<label><A class="htmldirectory" HREF="' . $this->m_bmkUri . '">' . $this->m_title . '</A></label>' : '<label>' . $this->m_title . '</label>';
            }
        }
        if ($level < $maxlevel) {
            if (count($this->m_listchildren)) { self::writelevel($level, $result); $result .= "<UL>\n"; }
            foreach ($this->m_listchildren as $child) { $child->writecss3treeview($level + 1, $result, $short, $maxlevel, $showkeyword, $openlevel, $minlevel, $type); }
            if (count($this->m_listchildren)) { self::writelevel($level, $result); $result .= "</UL>\n"; }
        }
        if ($level > 0 && $level < $maxlevel) { self::writelevel($level, $result); $result .= '</LI>'; }
    }

    public function writewiki($level, &$result)
    {
        if ($level !== 0) { $result .= "\n" . str_repeat('*', $level) . $this->m_title; }
        foreach ($this->m_listchildren as $child) { $child->writewiki($level + 1, $result); }
    }

    public function writewikitext($level, &$result)
    {
        if ($level !== 0) { $result .= "\n" . str_repeat('*', $level) . $this->m_title; }
        foreach ($this->m_listchildren as $child) { $child->writewikitext($level + 1, $result); }
    }

    public function writejson($level, &$result, $full = false, $encode = true)
    {
        $children = array();
        foreach ($this->m_listchildren as $child) {
            if ($full) { $res = ''; $child->writejson($level + 1, $res, $full, false); $children[] = $res; }
            else { $children[] = $child->getid(); }
        }
        $fields = array('id' => $this->m_id, 'type' => 'folder', 'title' => $this->m_title,
            'description' => $this->m_description, 'children' => $children,
            'parentid' => $this->m_parent ? $this->m_parent->getid() : -1,
            'parentName' => $this->m_parent ? $this->m_parent->gettitle() : '', 'sortindex' => $this->m_sortindex);
        if ($encode) { $result .= json_encode($fields); } else { $result = $fields; }
    }

    public function writemenuitem($level, &$result)
    {
        if ($level !== 0) { self::writelevel($level, $result); }
        $result .= 'new MenuItem("#","' . $this->m_title . '","", "_blank"' . "\n";
        foreach ($this->m_listchildren as $child) { self::writelevel($level + 2, $result); $result .= ','; $child->writemenuitem($level + 1, $result); }
        if ($level !== 0) { self::writelevel($level, $result); }
        $result .= ")\n";
    }
}
