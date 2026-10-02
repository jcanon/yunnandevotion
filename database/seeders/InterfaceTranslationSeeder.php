<?php

namespace Database\Seeders;

use App\Models\InterfaceTranslation;
use Illuminate\Database\Seeder;

class InterfaceTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $strings = [
            'brand' => ['Yunnan Devotional Atlas', '云南信仰空间图谱'],
            'home.headline' => ['Small places. Lasting records.', '微小的空间，长久的记录。'],
            'home.intro' => ['Documenting and preserving everyday Buddhist spaces.', '记录与保存日常生活中的佛教空间。'],
            'build.status' => ['Application foundation in development', '应用基础正在开发中'],
            'build.description' => ['The bilingual database foundation is running. Public records and account workflows are being built.', '双语数据库基础已运行。公开档案与账户功能正在开发中。'],
            'language.label' => ['Interface language', '界面语言'],
        ];
        foreach ($strings as $key => [$en, $zh]) {
            foreach (['en' => $en, 'zh-Hans' => $zh] as $locale => $value) {
                // Re-running deployment seeds must preserve the owner's text edits.
                InterfaceTranslation::firstOrCreate(compact('key', 'locale'), compact('value'));
            }
        }
    }
}
