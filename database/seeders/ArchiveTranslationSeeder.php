<?php

namespace Database\Seeders;

use App\Models\InterfaceTranslation;
use Illuminate\Database\Seeder;

class ArchiveTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            'archive' => ['Explore the atlas', '浏览图谱'], 'archive.history' => ['Site history', '地点历史'], 'archive.contribute' => ['Add an observation', '添加观察记录'], 'archive.mine' => ['My submissions', '我的提交'], 'archive.submission' => ['Submission details', '提交详情'],
            'archive.new' => ['Document a new site', '记录新地点'], 'archive.empty' => ['No records are available yet.', '暂无记录。'], 'archive.saved' => ['Observation recorded. Contributor submissions remain private until approved.', '观察记录已保存。贡献者的提交在批准前保持私密。'], 'archive.decision-saved' => ['Review decision recorded.', '审核决定已记录。'],
            'archive.name_en' => ['Site name — English', '地点名称 — 英文'], 'archive.name_zh' => ['Site name — Simplified Chinese', '地点名称 — 简体中文'], 'archive.description_en' => ['Observation — English', '观察描述 — 英文'], 'archive.description_zh' => ['Observation — Simplified Chinese', '观察描述 — 简体中文'], 'archive.source_en' => ['Sources — English', '来源 — 英文'], 'archive.source_zh' => ['Sources — Simplified Chinese', '来源 — 简体中文'],
            'archive.category' => ['Classification', '分类'], 'archive.condition' => ['Observed condition', '观察时的状态'], 'archive.date_precision' => ['Date precision', '日期精度'], 'archive.observed_from' => ['Observation date / range start', '观察日期／范围开始'], 'archive.observed_to' => ['Range end', '范围结束'], 'archive.latitude' => ['Latitude', '纬度'], 'archive.longitude' => ['Longitude', '经度'], 'archive.location_visibility' => ['Public location detail', '公开位置精度'], 'archive.location_notes' => ['Public location description', '公开位置描述'],
            'archive.exact' => ['Exact', '精确'], 'archive.approximate' => ['Approximate — coordinates withheld', '大致位置 — 不公开坐标'], 'archive.range' => ['Date range', '日期范围'], 'archive.unknown' => ['Unknown', '未知'],
            'archive.shrine' => ['Shrine', '小祠'], 'archive.altar' => ['Altar', '供台'], 'archive.incense' => ['Incense structure', '香火设施'], 'archive.niche' => ['Devotional niche', '佛龛'], 'archive.other' => ['Other', '其他'], 'archive.intact' => ['Intact', '完好'], 'archive.altered' => ['Altered', '已改建'], 'archive.relocated' => ['Relocated', '已迁移'], 'archive.demolished' => ['Demolished', '已拆除'],
            'archive.pending' => ['Awaiting review', '等待审核'], 'archive.approved' => ['Approved', '已批准'], 'archive.rejected' => ['Rejected', '已拒绝'], 'archive.changes_requested' => ['Changes requested', '需要修改'],
            'archive.submit' => ['Submit for review', '提交审核'], 'archive.publish' => ['Publish observation', '发布观察记录'], 'archive.reason' => ['Reason / message to contributor', '原因／给贡献者的留言'], 'archive.approve' => ['Approve', '批准'], 'archive.return' => ['Request changes', '要求修改'], 'archive.reject' => ['Reject', '拒绝'], 'archive.revise' => ['Revise and resubmit', '修改并重新提交'],
            'archive.language-error' => ['Provide a name and observation in at least one language.', '请至少使用一种语言填写名称和观察描述。'], 'archive.date-error' => ['Enter a valid past or present date; date ranges must be in order.', '请输入有效的过去或当日日期，日期范围须按顺序填写。'], 'archive.coordinate-error' => ['Enter both coordinates with latitude −90 to 90 and longitude −180 to 180.', '请填写两个坐标：纬度为−90至90，经度为−180至180。'], 'archive.reason-error' => ['Explain why changes are needed or the submission is rejected.', '请说明需要修改或拒绝的原因。'],
            'archive.help' => ['A site has a permanent identity. Each visit adds a dated observation; earlier approved observations remain available. Text and location only in this release.', '每个地点具有永久标识。每次访问新增带日期的观察，先前批准的记录将保留。本版本仅支持文字与位置。'],
            'archive.privacy' => ['Only enter public information in the location description. Approximate locations do not expose the stored coordinates.', '位置描述中仅填写可公开的信息。大致位置不会公开已存储的坐标。'],
            'archive.fallback' => ['Missing translations display the available original text. No machine translation is performed yet.', '缺少翻译时显示现有原文。目前尚未执行机器翻译。'],
            'archive.revision' => ['Revision of an earlier submission; previous content and decisions remain preserved.', '对先前提交的修订；原内容及审核决定仍保留。'],
            'archive.author' => ['Contributor account (private)', '贡献者账户（私密）'], 'archive.review-help' => ['Review the text, dates, sources and location privacy before approving.', '批准前请检查文字、日期、来源及位置隐私。'],
        ];
        foreach ($pairs as $key => [$en,$zh]) {
            foreach (['en' => $en, 'zh-Hans' => $zh] as $locale => $value) {
                InterfaceTranslation::firstOrCreate(compact('key', 'locale'), compact('value'));
            }
        }
    }
}
