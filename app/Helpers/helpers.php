<?php

use App\Helpers\SearchHelper;

if (!function_exists('highlightSearchTerm')) {
    /**
     * 高亮搜索关键词
     */
    function highlightSearchTerm($text, $searchTerm)
    {
        return SearchHelper::highlightSearchTerm($text, $searchTerm);
    }
}

if (!function_exists('extractSearchSnippet')) {
    /**
     * 提取搜索摘要
     */
    function extractSearchSnippet($content, $searchTerm, $maxLength = 200)
    {
        return SearchHelper::extractSearchSnippet($content, $searchTerm, $maxLength);
    }
}

if (!function_exists('cleanSearchQuery')) {
    /**
     * 清理搜索查询
     */
    function cleanSearchQuery($query)
    {
        return SearchHelper::cleanSearchQuery($query);
    }
}
