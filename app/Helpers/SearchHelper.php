<?php

namespace App\Helpers;

class SearchHelper
{
    /**
     * 在文本中高亮搜索关键词
     */
    public static function highlightSearchTerm($text, $searchTerm)
    {
        if (empty($searchTerm) || empty($text)) {
            return $text;
        }
        
        // 转义特殊字符
        $searchTerm = preg_quote($searchTerm, '/');
        
        // 高亮匹配的文本
        $highlighted = preg_replace(
            '/(' . $searchTerm . ')/i',
            '<mark class="bg-yellow-200 text-yellow-900 px-1 rounded">$1</mark>',
            $text
        );
        
        return $highlighted;
    }
    
    /**
     * 提取搜索摘要
     */
    public static function extractSearchSnippet($content, $searchTerm, $maxLength = 200)
    {
        if (empty($searchTerm) || empty($content)) {
            return substr($content, 0, $maxLength) . (strlen($content) > $maxLength ? '...' : '');
        }
        
        $searchTerm = preg_quote($searchTerm, '/');
        
        // 查找关键词位置
        if (preg_match('/(.{0,50})(' . $searchTerm . ')(.{0,50})/i', $content, $matches)) {
            $snippet = $matches[1] . $matches[2] . $matches[3];
            
            // 如果前面还有内容，添加省略号
            if (strlen($matches[1]) == 50 && strpos($content, $matches[1]) > 0) {
                $snippet = '...' . $snippet;
            }
            
            // 如果后面还有内容，添加省略号
            if (strlen($matches[3]) == 50 && strlen($content) > strlen($matches[0])) {
                $snippet = $snippet . '...';
            }
            
            return $snippet;
        }
        
        // 如果没有找到关键词，返回开头部分
        return substr($content, 0, $maxLength) . (strlen($content) > $maxLength ? '...' : '');
    }
    
    /**
     * 清理搜索查询
     */
    public static function cleanSearchQuery($query)
    {
        // 移除多余的空格
        $query = trim(preg_replace('/\s+/', ' ', $query));
        
        // 移除特殊字符（保留基本的搜索字符）
        $query = preg_replace('/[^\w\s\-\+\"\']/u', '', $query);
        
        return $query;
    }
    
    /**
     * 分析搜索查询
     */
    public static function parseSearchQuery($query)
    {
        $result = [
            'terms' => [],
            'phrases' => [],
            'excluded' => [],
            'original' => $query
        ];
        
        // 提取引号中的短语
        if (preg_match_all('/"([^"]+)"/', $query, $matches)) {
            $result['phrases'] = $matches[1];
            $query = preg_replace('/"[^"]+"/', '', $query);
        }
        
        // 提取排除的词（以 - 开头）
        if (preg_match_all('/\s-(\w+)/', $query, $matches)) {
            $result['excluded'] = $matches[1];
            $query = preg_replace('/\s-\w+/', '', $query);
        }
        
        // 剩余的词作为普通搜索词
        $terms = array_filter(explode(' ', trim($query)));
        $result['terms'] = $terms;
        
        return $result;
    }
    
    /**
     * 生成搜索建议
     */
    public static function generateSearchSuggestions($query, $limit = 10)
    {
        $suggestions = [];
        
        // 这里可以实现更复杂的建议算法
        // 比如基于用户历史、热门搜索、相似词等
        
        return $suggestions;
    }
    
    /**
     * 计算搜索相关性得分
     */
    public static function calculateRelevanceScore($gist, $searchTerms)
    {
        $score = 0;
        
        foreach ($searchTerms as $term) {
            // 标题匹配得分最高
            if (stripos($gist->title, $term) !== false) {
                $score += 10;
            }
            
            // 描述匹配
            if (stripos($gist->description, $term) !== false) {
                $score += 5;
            }
            
            // 内容匹配
            if (stripos($gist->content, $term) !== false) {
                $score += 3;
            }
            
            // 标签匹配
            foreach ($gist->tags as $tag) {
                if (stripos($tag->name, $term) !== false) {
                    $score += 7;
                }
            }
            
            // 文件名匹配
            if (stripos($gist->filename, $term) !== false) {
                $score += 6;
            }
        }
        
        return $score;
    }
}
