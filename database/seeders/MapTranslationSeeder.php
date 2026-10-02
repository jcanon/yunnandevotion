<?php

namespace Database\Seeders;

use App\Models\InterfaceTranslation;
use Illuminate\Database\Seeder;

class MapTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            'map.close' => ['Close map popup', '关闭地图弹窗'],
            'map.title' => ['Map', '地图'], 'map.list' => ['List', '列表'], 'map.search' => ['Search names or descriptions', '搜索名称或描述'], 'map.as-of' => ['Documented as of', '截至日期的记录'], 'map.all' => ['All', '全部'], 'map.apply' => ['Apply filters', '应用筛选'], 'map.reset' => ['Reset filters', '重置筛选'],
            'map.history-help' => ['This view uses currently approved observations dated on or before your selected date. Undated observations and date ranges ending after that date are excluded. It does not reconstruct what was published at the time.', '此视图使用当前已批准且日期不晚于所选日期的观察。无日期记录及结束日期晚于该日期的范围记录不包含在内。这不是对当时网站发布内容的重建。'],
            'map.current' => ['Showing the latest eligible observation for each site.', '显示每个地点最近的符合条件的观察。'],
            'map.scope' => ['Markers show this results page only. Sites with withheld or missing coordinates remain in the list.', '标记仅显示本页结果。坐标未公开或缺失的地点仍在列表中。'],
            'map.failed' => ['Map tiles are unavailable. The list and manual coordinate fields still work.', '地图图块不可用。列表和手动坐标字段仍可使用。'],
            'map.pick' => ['Click a point, drag the pin, or use the map center. Manual coordinates remain available.', '点击位置、拖动标记或使用地图中心。也可手动输入坐标。'],
            'map.add-here' => ['Add a record here', '在此添加记录'], 'map.center' => ['Use map center', '使用地图中心'], 'map.locate' => ['Use my current location', '使用我的当前位置'], 'map.location-error' => ['Location could not be obtained. Choose a map point or enter coordinates manually.', '无法获取当前位置。请选择地图位置或手动输入坐标。'],
            'map.selected' => ['Selected coordinates', '已选坐标'], 'map.zoom-in' => ['Zoom in', '放大'], 'map.zoom-out' => ['Zoom out', '缩小'], 'map.show' => ['Show map', '显示地图'], 'map.privacy' => ['Only explicitly public coordinates are sent to the public map. Choosing a submission location does not publish it.', '公开地图仅接收明确允许公开的坐标。选择提交位置不会将其发布。'],
            'map.legend' => ['Marker key', '标记图例'], 'map.clear' => ['Clear coordinates', '清除坐标'],
        ];
        foreach ($pairs as $key => [$en,$zh]) {
            foreach (['en' => $en, 'zh-Hans' => $zh] as $locale => $value) {
                InterfaceTranslation::firstOrCreate(compact('key', 'locale'), compact('value'));
            }
        }
    }
}
