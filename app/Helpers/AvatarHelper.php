<?php

namespace App\Helpers;

class AvatarHelper
{
    /**
     * 生成用户头像的初始字母
     */
    public static function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        
        if (count($words) >= 2) {
            // 如果有多个单词，取前两个单词的首字母
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            // 如果只有一个单词，取前两个字符
            return strtoupper(substr($name, 0, 2));
        }
    }

    /**
     * 根据用户名生成背景颜色
     */
    public static function getBackgroundColor(string $name): string
    {
        $colors = [
            '#FF6B6B', // 红色
            '#4ECDC4', // 青色
            '#45B7D1', // 蓝色
            '#96CEB4', // 绿色
            '#FFEAA7', // 黄色
            '#DDA0DD', // 紫色
            '#98D8C8', // 薄荷绿
            '#F7DC6F', // 金黄色
            '#BB8FCE', // 淡紫色
            '#85C1E9', // 天蓝色
            '#F8C471', // 橙色
            '#82E0AA', // 浅绿色
            '#F1948A', // 粉红色
            '#85C1E9', // 浅蓝色
            '#D7BDE2', // 淡紫色
        ];

        // 根据用户名的哈希值选择颜色
        $hash = crc32($name);
        $index = abs($hash) % count($colors);
        
        return $colors[$index];
    }

    /**
     * 获取文字颜色（根据背景色自动选择黑色或白色）
     */
    public static function getTextColor(string $backgroundColor): string
    {
        // 移除 # 号
        $hex = ltrim($backgroundColor, '#');
        
        // 转换为 RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // 计算亮度
        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
        
        // 如果亮度大于 128，使用黑色文字，否则使用白色文字
        return $brightness > 128 ? '#000000' : '#FFFFFF';
    }

    /**
     * 生成头像的内联样式
     */
    public static function getAvatarStyle(string $name): string
    {
        $backgroundColor = self::getBackgroundColor($name);
        $textColor = self::getTextColor($backgroundColor);
        
        return "background-color: {$backgroundColor}; color: {$textColor};";
    }

    /**
     * 生成头像的CSS类名
     */
    public static function getAvatarClass(string $name): string
    {
        $hash = crc32($name);
        $index = abs($hash) % 15; // 15种颜色
        
        return "avatar-color-{$index}";
    }
}
