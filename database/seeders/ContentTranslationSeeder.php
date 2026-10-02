<?php

namespace Database\Seeders;

use App\Models\InterfaceTranslation;
use Illuminate\Database\Seeder;

class ContentTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            'archive.home' => ['A living archive of devotional places', '信仰空间的持续记录'],
            'content.title' => ['Homepage content', '首页内容'],
            'translations.title' => ['Interface translations', '界面翻译'],
            'content.help' => ['Edit both languages and save each section separately. Changes become public immediately. Text is displayed as plain text. Keep any :placeholders unchanged. If another admin has saved changes, reload to compare them before trying again.', '请编辑两种语言，并分别保存每个部分。更改将立即公开，内容以纯文本显示。请保留所有 :placeholders 占位符。如果其他管理员已保存更改，请重新加载页面进行比较后再试。'],
            'content.view' => ['View homepage', '查看首页'],
            'content.search' => ['Search translation keys or text', '搜索翻译键名或文本'],
            'content.save' => ['Save both languages', '保存双语内容'],
            'content.saved' => ['Both languages have been saved.', '双语内容已保存。'],
            'content.conflict' => ['This section changed after you opened it. Copy your draft elsewhere, reload, and compare before saving again.', '此部分在您打开后已被修改。请先将草稿复制到其他位置，重新加载并比较后再保存。'],
            'content.tokens' => ['Keep the existing :placeholder tokens unchanged in each language.', '请保留各语言中原有的 :placeholder 占位符。'],
            'content.empty' => ['No matching translations.', '没有符合条件的翻译。'],
            'content.home.headline' => ['Headline', '主标题'],
            'content.home.intro' => ['Introduction', '简介'],
            'content.home.project' => ['About the project', '关于项目'],
            'content.home.funding' => ['Funding and acknowledgements', '资金支持与致谢'],
            'content.home.contributors' => ['Original contributors', '初始贡献者'],
            'home.project' => ['Yunnan Devotional Atlas documents everyday Buddhist spaces in Kunming, with future expansion across Yunnan. Dated observations preserve a record of places as they change, relocate, or disappear.', '云南信仰空间图谱记录昆明日常生活中的佛教空间，未来将拓展至云南其他地区。通过带有日期的观察记录，保存这些地点在改变、迁移或消失过程中的历史。'],
            'home.funding' => ['Funding and endowment acknowledgements will be added when confirmed.', '资金与捐赠基金的致谢信息将在确认后公布。'],
            'home.contributors' => ['Original contributors and project partners will be acknowledged here as the project develops.', '随着项目推进，我们将在此列出初始贡献者与项目合作伙伴。'],
        ];
        foreach ($pairs as $key => [$en,$zh]) {
            foreach (['en' => $en, 'zh-Hans' => $zh] as $locale => $value) {
                InterfaceTranslation::firstOrCreate(compact('key', 'locale'), compact('value'));
            }
        }
    }
}
