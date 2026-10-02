<?php

namespace Database\Seeders;

use App\Models\InterfaceTranslation;
use Illuminate\Database\Seeder;

class VideoTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            'video.hash-original' => ['Original SHA-256', '原件SHA-256'],
            'video.hash-web' => ['Web copy SHA-256', '网页版SHA-256'],
            'video.title' => ['Interview video', '访谈视频'], 'video.file' => ['Original video', '原始视频'], 'video.title_en' => ['Video title — English', '视频标题 — 英文'], 'video.title_zh' => ['Video title — Simplified Chinese', '视频标题 — 简体中文'], 'video.transcript_en' => ['Transcript — English', '文字稿 — 英文'], 'video.transcript_zh' => ['Transcript — Simplified Chinese', '文字稿 — 简体中文'],
            'video.help' => ['One MP4 or WebM per observation, up to 100 MB and 10 minutes. Hosting upload limits may be lower. Originals stay private; a moderator prepares the web copy. Transcripts may be added now or completed during review.', '每条观察可上传一个MP4或WebM，最多100MB和10分钟。主机上传限制可能更低。原件保持私密；审核员准备网页版。文字稿可现在添加或在审核时补全。'],
            'video.permission' => ['I have permission from the interview participants and rights holder to publish this recording.', '我已获得访谈参与者及权利人授权，可以发布此录音录像。'],
            'video.required' => ['Provide the required title, credit and consent. Approval requires a prepared web copy, both transcripts and caption tracks, scan evidence, and completed checks.', '请提供所需标题、署名及授权。批准前须准备网页版、双语文字稿和字幕轨道、扫描证据并完成检查。'],
            'video.invalid' => ['Use an MP4 or WebM up to 100 MB. Duration must be 1–600 seconds; text must stay within the field limit.', '请使用不超过100MB的MP4或WebM。时长须为1至600秒；文字不得超过字段限制。'],
            'video.prepare' => ['Prepare web video and translations', '准备网页视频与翻译'], 'video.web' => ['Prepared MP4 or WebM (optional when replacing only text)', '准备好的MP4或WebM（仅修改文字时可不上传）'], 'video.duration' => ['Verified duration in seconds', '已核实的时长（秒）'], 'video.captions_en' => ['Timed captions — English (WebVTT)', '定时字幕 — 英文（WebVTT）'], 'video.captions_zh' => ['Timed captions — Simplified Chinese (WebVTT)', '定时字幕 — 简体中文（WebVTT）'],
            'video.vtt-help' => ['Use WEBVTT, a blank line, then cues such as 00:00:00.000 --> 00:00:03.000 followed by caption text. Separate cues with blank lines. No markup; cue times must be ordered and within the video duration.', '以WEBVTT开头，空一行，然后输入时间如00:00:00.000 --> 00:00:03.000，下一行为字幕文字。字幕段之间空一行。不得使用标记；时间须有序且不超过视频时长。'],
            'video.vtt-invalid' => ['Enter valid plain WebVTT cues within the video duration.', '请输入有效的纯文本WebVTT字幕，时间不得超过视频时长。'],
            'video.manual' => ['Manual preparation: check playback and duration, remove private metadata, and scan both originals and web copies externally. No automatic transcoding, duration measurement, transcription, translation or malware scanning is connected.', '人工准备：检查播放及时长，移除私密元数据，并在外部扫描原件与网页版。尚未连接自动转码、时长测量、转写、翻译或恶意软件扫描。'],
            'video.save' => ['Save prepared video and text', '保存准备好的视频与文字'], 'video.prepared' => ['Prepared material saved privately. Review is still required.', '准备好的材料已私密保存。仍需审核。'], 'video.reviewed' => ['Interview review recorded.', '访谈审核已记录。'], 'video.blocked' => ['Every photograph and interview must be approved before publication.', '发布前必须批准全部照片与访谈。'],
            'video.waiting' => ['A web copy has not been prepared yet.', '网页版尚未准备。'], 'video.checks' => ['Both files passed external scans. I checked browser playback, duration of at most 10 minutes, captions, transcripts, consent, credits and privacy.', '两个文件均已通过外部扫描。我已检查浏览器播放、时长不超过10分钟、字幕、文字稿、授权、署名及隐私。'],
            'video.original' => ['Download private original for preparation', '下载私密原件以准备处理'], 'video.evidence' => ['Scan tools, dates, operator, both checksums and results / rejection reason', '扫描工具、日期、操作人员、两个校验和及结果／拒绝原因'],
        ];
        foreach ($pairs as $key => [$en,$zh]) {
            foreach (['en' => $en, 'zh-Hans' => $zh] as $locale => $value) {
                InterfaceTranslation::firstOrCreate(compact('key', 'locale'), compact('value'));
            }
        }
    }
}
