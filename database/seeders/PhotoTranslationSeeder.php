<?php

namespace Database\Seeders;

use App\Models\InterfaceTranslation;
use Illuminate\Database\Seeder;

class PhotoTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            'photo.archive-help' => ['Each visit adds a dated observation to a permanent site. Earlier approved observations remain available. Photographs stay private until reviewed.', '每次访问为永久地点新增带日期的观察。先前批准的观察仍可查看。照片在审核前保持私密。'],
            'photo.title' => ['Photographs', '照片'], 'photo.help' => ['Up to four JPEG, PNG or WebP photographs, 5 MB and 16 megapixels each. Originals stay private. Photos require attachment review before publication, including moderator uploads.', '最多四张JPEG、PNG或WebP照片，每张不超过5MB和1600万像素。原件保持私密。包括审核员上传的照片在内，所有照片均须审核后才能发布。'],
            'photo.file' => ['Choose photograph', '选择照片'], 'photo.caption_en' => ['Caption — English', '说明 — 英文'], 'photo.caption_zh' => ['Caption — Simplified Chinese', '说明 — 简体中文'], 'photo.credit' => ['Photographer / source credit (public)', '摄影者／来源署名（公开）'], 'photo.permission' => ['I have permission to publish this photograph.', '我有权发布此照片。'],
            'photo.required' => ['Include a caption in at least one language, credit and permission. For approval, record scan evidence and confirm all checks.', '请填写至少一种语言的说明、署名并确认授权。批准时须记录扫描证据并确认全部检查。'],
            'photo.invalid' => ['Use a valid JPEG, PNG or WebP, at most 5 MB, 6000 pixels per side and 16 megapixels.', '请使用有效的JPEG、PNG或WebP，每张不超过5MB、单边6000像素及1600万像素。'],
            'photo.blocked' => ['Every photograph must be approved before this observation can be published. Return the submission if a photograph needs replacement.', '发布观察记录前必须批准全部照片。如照片需要替换，请退回提交。'],
            'photo.evidence' => ['Manual scan evidence / rejection reason', '人工扫描证据／拒绝原因'], 'photo.scan-help' => ['Record the scanner, scan date, result and operator for the checksum shown. This form does not run a scanner.', '请根据所示校验和记录扫描工具、日期、结果和操作人员。此表单不会执行扫描。'],
            'photo.checks' => ['Scan passed; image content, permission, credit and location privacy reviewed.', '扫描已通过；已审核图片内容、授权、署名及位置隐私。'],
            'photo.original' => ['Download private original for checking', '下载私密原件以检查'], 'photo.reviewed' => ['Attachment decision recorded.', '附件审核决定已记录。'], 'photo.resubmit' => ['For a revision, select the photographs again. Previous attachments remain with the earlier submission.', '修订时请重新选择照片。原附件将随先前提交保留。'],
            'photo.reselect' => ['If validation fails, select files again before resubmitting.', '如果验证失败，请在重新提交前再次选择文件。'],
        ];
        foreach ($pairs as $key => [$en,$zh]) {
            foreach (['en' => $en, 'zh-Hans' => $zh] as $locale => $value) {
                InterfaceTranslation::firstOrCreate(compact('key', 'locale'), compact('value'));
            }
        }
    }
}
