<?php

namespace Database\Seeders;

use App\Models\InterfaceTranslation;
use Illuminate\Database\Seeder;

class ResearchTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            'research.title' => ['Research downloads', '研究数据下载'],
            'research.help' => ['Download all matching sites, with the latest eligible observation per site and both languages. Current filters apply; pagination does not. Media files are not included. Private coordinates are omitted.', '下载所有符合筛选条件的地点，每个地点包含最新的符合日期条件的观察记录及中英双语文本。不受分页限制，不包含媒体文件，隐藏的坐标不会导出。'],
            'research.csv' => ['Download CSV', '下载 CSV'],
            'research.geojson' => ['Download GeoJSON', '下载 GeoJSON'],
            'research.format' => ['CSV uses stable English column keys and UTF-8 text. Potential spreadsheet formulas are prefixed with an apostrophe; GeoJSON retains original text. GeoJSON points use WGS 84 longitude, latitude; records without public coordinates have no geometry.', 'CSV 使用固定的英文列名和 UTF-8 编码；可能被电子表格识别为公式的文本前会添加单引号，GeoJSON 保留原始文本。GeoJSON 点采用 WGS 84 经度、纬度；无公开坐标的记录不包含几何位置。'],
            'research.cite' => ['Cite this observation', '引用此观察记录'],
            'research.download-citation' => ['Download citation (.txt)', '下载引用文本（.txt）'],
            'research.observed' => ['Observed', '观察日期'],
            'research.accessed' => ['Accessed', '访问日期'],
            'research.citation-help' => ['This citation identifies this dated observation. The permanent site page also preserves other observations.', '此引用标识当前的观察记录。该地点的永久页面也保留其他观察记录。'],
        ];
        foreach ($pairs as $key => [$en, $zh]) {
            foreach (['en' => $en, 'zh-Hans' => $zh] as $locale => $value) {
                InterfaceTranslation::firstOrCreate(compact('key', 'locale'), compact('value'));
            }
        }
    }
}
