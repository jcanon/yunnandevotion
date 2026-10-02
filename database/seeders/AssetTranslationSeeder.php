<?php

namespace Database\Seeders;

use App\Models\InterfaceTranslation;
use Illuminate\Database\Seeder;

class AssetTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            'assets.title' => ['Categories and images', '分类与图片'],
            'assets.help' => ['Category identifiers remain permanent. Edit both names and the marker color. Optional SVGs must contain simple shapes with a viewBox (50 KB maximum); scripts, external resources, styles and text are not accepted. Changes appear immediately.', '分类标识符永久保留。可编辑双语名称和标记颜色。可选 SVG 必须包含 viewBox 和简单图形（最大 50 KB），不接受脚本、外部资源、样式或文本。更改立即生效。'],
            'assets.new' => ['Add category', '添加分类'], 'assets.key' => ['Permanent identifier (lowercase letters, digits, hyphens)', '永久标识符（小写字母、数字、连字符）'],
            'assets.color' => ['Marker color', '标记颜色'], 'assets.svg' => ['Upload SVG marker', '上传 SVG 标记'], 'assets.reset' => ['Restore the default marker shape', '恢复默认标记形状'],
            'assets.invalid' => ['Use a unique lowercase category identifier and a valid six-digit hex color.', '请使用唯一的小写分类标识符和有效的六位十六进制颜色。'],
            'assets.svg-error' => ['This SVG is not supported. Export simple paths/shapes with a viewBox and no styles, scripts or external references.', '不支持此 SVG。请导出包含 viewBox 的简单路径或图形，不使用样式、脚本或外部引用。'],
            'assets.photos' => ['Homepage photographs', '首页照片'], 'assets.alt' => ['Image description', '图片描述'],
            'assets.photo-help' => ['Upload JPEG, PNG or WebP up to 5 MB, 6000 pixels per side and 16 megapixels. Both language descriptions and a credit are required. A web JPEG is published immediately; originals are not retained here. To replace an image, upload its replacement and remove the old image. These are homepage illustrations, separate from preserved archive media.', '可上传 JPEG、PNG 或 WebP，最大 5 MB、每边 6000 像素及总计 1600 万像素。必须提供双语描述和署名。网页 JPEG 将立即发布，此处不保留原件。替换图片时，请先上传新图片，再移除旧图片。首页配图与永久保存的档案媒体分开管理。'],
            'assets.permission' => ['I have permission to publish this photograph and have checked its content.', '我有权发布此照片，并已检查其内容。'],
            'assets.upload' => ['Publish photograph', '发布照片'], 'assets.remove' => ['Remove homepage photograph', '移除首页照片'],
        ];
        foreach ($pairs as $key => [$en,$zh]) {
            foreach (['en' => $en, 'zh-Hans' => $zh] as $locale => $value) {
                InterfaceTranslation::firstOrCreate(compact('key', 'locale'), compact('value'));
            }
        }
    }
}
