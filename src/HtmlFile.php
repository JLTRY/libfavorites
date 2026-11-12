<?php

namespace JLTRY\LibFavorites;

\defined('_JEXEC') or die;

class HtmlFile extends HtmlItem
{
    protected $m_loadInSidebar = false;
    protected $m_pos = 0;
    protected $m_queryId = 0;
    protected $m_tags = array();
    protected $m_keyword = '';
    protected $m_iconUri = '';

    public function __construct($parent, $params)
    {
        parent::__construct($parent, $params);
        foreach (get_class_vars(__CLASS__) as $attr => $value) {
            $field = substr($attr, 2);
            if (array_key_exists($field, $params)) {
                $this->$attr = $params[$field];
            }
        }
    }

    public function gettype() { return 'file'; }

    public function geturi($check = false, $encode = true)
    {
        if (!$check) { return $this->m_bmkUri; }
        $result = CheckUrl::checkurl($this->m_bmkUri);
        if ($result === true) { return $encode ? json_encode(true) : true; }
        $result = array('m_id' => $this->m_id, 'm_title' => $this->m_title, 'm_path' => $this->getpath(), 'm_bmkUri' => $this->m_bmkUri, 'm_checkurl' => $result);
        return $encode ? json_encode($result) : $result;
    }

    public function modify($params)
    {
        foreach (array('url' => 'm_bmkUri', 'keyword' => 'm_keyword') as $key => $property) {
            if (array_key_exists($key, $params) && $this->$property != $params[$key]) { $this->$property = $params[$key]; $this->m_modified = true; }
        }
        parent::modify($params);
    }

    public function __toresult() { return '<DT><A HREF="' . $this->m_bmkUri . '">' . $this->m_title . '</a>'; }

    public function writehtml($level, &$result, $short = false, $maxlevel = 9999, $showkeyword = false, $openlevel = 9999)
    {
        if ($this->m_type === 'separator' || $this->m_type === 'text/x-moz-place-separator') { $result .= '<hr>'; return; }
        if ($level === 0 && !$short) { $result .= '<UL>'; }
        self::writelevel($level, $result);
        if (!$short) { $result .= '<LI>'; }
        $keyword = $showkeyword && $this->m_keyword !== '' ? '[' . strtoupper($this->m_keyword) . ']' : '';
        $link = '<a HREF="' . $this->m_bmkUri . '" icon="' . $this->m_iconUri . '">' . $this->m_title . '</a>';
        $result .= !$short ? sprintf('%s <sup><small>%s</small></sup>', $link, $keyword) : ($keyword !== '' ? '<a HREF="' . $this->m_bmkUri . '" icon="' . $this->m_iconUri . '"><small>' . $keyword . '</small></a>' : $link);
        if ($this->m_description !== '' && !$short) { self::writelevel($level, $result); $result .= '<DD>' . $this->m_description . "\n"; }
        if (!$short) { $result .= '</LI>'; }
        if ($level === 0 && !$short) { $result .= '</UL>'; }
    }

    public function writecss3treeview($level, &$result, $short = false, $maxlevel = 9999, $showkeyword = false, $openlevel = 1, $minlevel = 1, $type = 'all')
    {
        if ($type !== 'all' && $type !== 'file') { return; }
        if ($this->m_type === 'separator' || $this->m_type === 'text/x-moz-place-separator') { $result .= '<hr>'; return; }
        if ($level === 0 && !$short) { $result .= '<UL>'; }
        self::writelevel($level, $result);
        if (!$short) { $result .= '<LI>'; }
        $keyword = $showkeyword && $this->m_keyword !== '' ? '[' . strtoupper($this->m_keyword) . ']' : '';
        $link = '<a class="htmlfile" HREF="' . $this->m_bmkUri . '" icon="' . $this->m_iconUri . '">' . $this->m_title . '</a>';
        $result .= !$short ? sprintf('%s <sup><small>%s</small></sup>', $link, $keyword) : ($keyword !== '' ? '<a class="htmlfile" HREF="' . $this->m_bmkUri . '" icon="' . $this->m_iconUri . '"><sup>' . $keyword . '</sup></a>' : $link);
        if ($this->m_description !== '' && !$short) { self::writelevel($level, $result); $result .= '<DD>' . $this->m_description . "\n"; }
        if (!$short) { $result .= '</LI>'; }
        if ($level === 0 && !$short) { $result .= '</UL>'; }
    }

    public function writewiki($level, &$result)
    {
        if ($level !== 0) { $result .= "\n" . str_repeat('*', $level); }
        $result .= sprintf('<a HREF="%s">%s</a>', $this->m_bmkUri, $this->m_title);
        if ($this->m_description !== '') { $result .= '<br>' . $this->m_description; }
    }

    public function writewikitext($level, &$result) { $result .= "\n" . str_repeat('*', max(0, $level)) . sprintf('[%s %s]', $this->m_bmkUri, $this->m_title); }
    public function writewikiimagelink($image, &$result) { $result = sprintf('[[%s|link=%s]][%s %s]', $image, $this->m_bmkUri, $this->m_bmkUri, $this->m_title); }

    public function writejson($level, &$result, $full, $encode = true)
    {
        $fields = array('id' => $this->m_id, 'type' => $this->m_type, 'title' => $this->m_title,
            'parentName' => $this->m_parent ? $this->m_parent->gettitle() : '', 'bmkUri' => $this->m_bmkUri,
            'tags' => $this->m_tags, 'keyword' => $this->m_keyword, 'description' => $this->m_description,
            'loadInSidebar' => false, 'parentid' => $this->m_parent ? $this->m_parent->getid() : -1,
            'sortindex' => $this->m_sortindex);
        if ($encode) { $result .= json_encode($fields); } else { $result = $fields; }
    }

    public function writemenuitem($level, &$result) { $result .= 'new MenuItem("' . $this->m_bmkUri . '","' . $this->m_title . '","", "_blank")' . "\n"; }
}
