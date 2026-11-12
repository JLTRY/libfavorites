<?php

namespace JLTRY\LibFavorites;

\defined('_JEXEC') or die;

class HtmlFavorites
{
    public static function convertNodeToJson($node, $parent)
    {
        if (in_array(strtoupper($node->nodeName), array('DL', 'DT', 'H3', 'A'))) {
            switch (strtoupper($node->nodeName)) {
                case 'H3':
                    $item = $parent === null ? new HtmlRoot(null, null) : new HtmlDirectory($parent, array());
                    $item->modify(array('title' => trim($node->nodeValue)));
                    $parent = $item;
                    break;
                case 'A':
                    new HtmlFile($parent, array('title' => trim($node->nodeValue)));
                    break;
            }
        }

        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $parent = self::convertNodeToJson($child, $parent);
                if (strtoupper($child->nodeName) === 'DL' && $parent) { $parent = $parent->getparent(); }
            }
        }
        return $parent;
    }

    public static function converthtmltoroot($file)
    {
        $dom = new \\DOMDocument();
        \\libxml_use_internal_errors(true);
        $dom->loadHTML(file_get_contents($file), \\LIBXML_NOERROR);
        \\libxml_clear_errors();
        $root = new HtmlRoot(-1, '');
        self::convertNodeToJson($dom->documentElement, $root);
        return $root;
    }
}
