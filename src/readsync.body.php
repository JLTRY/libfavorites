<?php

namespace JLTRY\LibFavorites;

\defined('_JEXEC') or die;

require_once __DIR__ . '/HtmlItem.php';
require_once __DIR__ . '/HtmlDirectory.php';
require_once __DIR__ . '/HtmlFile.php';
require_once __DIR__ . '/HtmlRoot.php';
require_once __DIR__ . '/HtmlFavorites.php';
require_once __DIR__ . '/JsonFavorites.php';

function readsync($params, &$result)
{
    if (array_key_exists('jsonfile', $params)) {
        $jsonfile = $params['jsonfile'];
        if (!file_exists($jsonfile)) { $result = 'File does not exist ' . $jsonfile; return; }
        $root = JsonFavorites::convertjsontoroot($jsonfile);
    } elseif (array_key_exists('htmlfile', $params)) {
        $htmlfile = $params['htmlfile'];
        if (!file_exists($htmlfile)) { $result = 'File does not exist ' . $htmlfile; return; }
        $root = HtmlFavorites::converthtmltoroot($htmlfile);
    } elseif (array_key_exists('serfile', $params)) {
        $serfile = $params['serfile'];
        if (!file_exists($serfile)) { $result = 'File does not exist ' . $serfile; return; }
        $root = HtmlRoot::load($serfile);
        if ($root === null) { $result = 'root is null : ' . $serfile; return; }
    } else {
        $root = null;
    }

    $children = array();
    $result = '';
    if (!is_array($params)) { $result = 'error:' . $params; return false; }
    if (array_key_exists('url', $params)) {
        $children = array(new HtmlFile(null, array('title' => isset($params['name']) ? $params['name'] : $params['url'], 'bmkUri' => $params['url'])));
        $field = 'url'; $find = true;
    } elseif (array_key_exists('path', $params)) {
        $path = explode('/', $params['path']); $find = $root ? $root->finditemwithpath($path, $children) : false; $field = 'path';
    } elseif (array_key_exists('like', $params)) {
        $find = $root ? $root->finditemnamed($params['like'], $children, true) : false; $field = 'like';
    } elseif (array_key_exists('tag', $params)) {
        $find = $root ? $root->finditemwithtag($params['tag'], $children) : false;
        if ($find) { $directory = new HtmlDirectory(null, array()); $directory->addChildren($children); $children = array($directory); }
        $field = 'tag';
    } elseif (array_key_exists('keyword', $params) && $params['keyword'] != 1) {
        $find = $root ? $root->finditemwithkeyword($params['keyword'], $children) : false; $field = 'keyword';
    } elseif (array_key_exists('name', $params)) {
        $find = $root ? $root->finditemnamed($params['name'], $children) : false; $field = 'name';
    } elseif (array_key_exists('id', $params)) {
        $find = $root ? $root->finditemwithid(urldecode($params['id']), $children) : false; $field = 'id';
    } else {
        if ($root !== null) { $children[] = $root; }
        $find = $root !== null;
    }

    $output = isset($params['output']) ? $params['output'] : 'html';
    $level = isset($params['level']) ? $params['level'] : 9999;
    $openlevel = isset($params['open']) ? $params['open'] : 9999;
    $keyword = array_key_exists('keyword', $params);
    $image = isset($params['image']) ? $params['image'] : 'File:Nuvola_apps_download_manager.png';
    $head = isset($params['head']) ? $params['head'] : '';
    $class = isset($params['class']) ? $params['class'] : '';

    if (!$find) { $result = 'not found :' . (isset($params[$field]) ? $params[$field] : '') . ':'; return -1; }
    if ($output === 'menuitem') { $result .= '<BODY BGCOLOR="#F9C784" OnLoad="FolderInit();ShowAll();"><script language="javascript" src="scripts/tools.js"></script><script language="javascript" src="scripts/menuitem.js"></script>'; }
    if ($output === 'menuitemdiv') { $result .= '<script language="javascript" src="scripts/tools.js"></script><script language="javascript" src="scripts/menuitem.js"></script>'; }
    if (preg_match('/css3treeview/', $output)) { $result .= '<div class="css-treeview ' . $class . '">' . $head; }

    foreach ($children as $child) {
        switch ($output) {
            case 'json': $child->writejson(0, $result, false); break;
            case 'jsonfull': $child->writejson(0, $result, true); break;
            case 'wiki': $child->writewiki(0, $result); break;
            case 'wikitext': $child->writewikitext(0, $result); break;
            case 'link': $result = $child->geturi(); break;
            case 'checkurl': ini_set('max_execution_time', 3600); $result = $child->geturi(true, true); break;
            case 'wikiimagelink': $child->writewikiimagelink($image, $result); break;
            case 'title': $result = $child->gettitle(); break;
            case 'html': $child->writehtml(0, $result, false, $level, $keyword); break;
            case 'htmlkw': $child->writehtml(0, $result, false, $level, true); break;
            case 'htmlshort': $child->writehtml(0, $result, true, $level, $keyword); break;
            case 'htmlshortkw': $child->writehtml(0, $result, true, $level, true); break;
            case 'menuitem':
            case 'menuitemdiv': $result .= '<script language="javascript">'; $child->writemenuitem(0, $result); $result .= '</script>'; break;
            case 'css3treeview': $child->writecss3treeview(0, $result, false, $level, $keyword, $openlevel); break;
            case 'css3treeviewshort': $child->writecss3treeview(0, $result, true, $level, $keyword, $openlevel); break;
            case 'css3treeviewkw': $child->writecss3treeview(0, $result, false, $level, true, $openlevel); break;
            case 'css3link': $result .= '<span class="css-treeview">'; $child->writecss3treeview(0, $result, true, $level, false, $openlevel); $result .= '</span>'; break;
            case 'css3linkkw': $result .= '<span class="css-treeview">'; $child->writecss3treeview(0, $result, true, $level, $keyword, $openlevel); $result .= '</span>'; break;
        }
        if (count($children) > 1 && strpos($output, 'css3') === false) { $result .= '<br>'; }
    }
    if ($output === 'menuitem') { $result .= '<script language="javascript">writetabMenuItemsTop(document);</script></BODY>'; }
    if ($output === 'menuitemdiv') { $result .= '<script language="javascript">writetabMenuItemsTop(document);FolderInit();ShowAll();</script>'; }
    if (preg_match('/css3treeview/', $output)) { $result .= '</div>'; }
    return 0;
}
