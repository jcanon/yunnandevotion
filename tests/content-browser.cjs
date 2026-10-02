const { chromium } = require('C:/Users/Jessie/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const { execFileSync } = require('node:child_process');
const { randomBytes } = require('node:crypto');
const assert = require('node:assert/strict');
const fs = require('node:fs');
(async () => {
  const env = { ...process.env, YDA_QA_EMAIL: `qa-${randomBytes(8).toString('hex')}@example.test`, YDA_QA_PASSWORD: randomBytes(18).toString('hex') };
  execFileSync('php', ['tests/profile-browser-fixture.php', 'create'], { env });
  const browser = await chromium.launch({ headless: true, channel: 'msedge' });
  try {
    const page = await browser.newPage(); const errors = [];
    page.on('pageerror', e => errors.push(e.message));
    await page.goto('http://127.0.0.1:8000/login?lang=en');
    await page.locator('[name=email]').fill(env.YDA_QA_EMAIL);
    await page.locator('[name=password]').fill(env.YDA_QA_PASSWORD);
    await page.locator('button.primary').click(); await page.waitForURL('**/profile');
    await page.goto('http://127.0.0.1:8000/admin/content?lang=en');
    await page.locator('textarea[name=en]').first().fill('Unsaved English draft');
    await page.locator('textarea[name=zh]').first().fill('未保存的中文草稿');
    await page.locator('a[lang="zh-Hans"]').click();
    await page.waitForFunction(() => document.documentElement.lang === 'zh-Hans');
    assert.equal(await page.locator('textarea[name=en]').first().inputValue(), 'Unsaved English draft');
    assert.equal(await page.locator('textarea[name=zh]').first().inputValue(), '未保存的中文草稿');
    await page.goto('http://127.0.0.1:8000/admin/translations?lang=en');
    await page.locator('input[name=search]').fill('language.label');
    await page.locator('form.filters button').focus(); await page.keyboard.press('Enter');
    await page.waitForURL('**/*search=language.label*');
    assert.equal(await page.locator('textarea[name=en]').count(), 1);
    assert.equal(await page.locator('textarea[name=zh]').inputValue(), '界面语言');
    fs.mkdirSync('storage/app/qa', { recursive: true });
    for (const lang of ['en','zh-Hans']) for (const width of [390,768,1440]) {
      await page.setViewportSize({ width, height: 900 });
      for (const [name,path] of [['home','/'],['content','/admin/content'],['translations','/admin/translations']]) {
        await page.goto('http://127.0.0.1:8000' + path + '?lang=' + lang);
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1), false, `${name}/${lang}/${width}`);
        await page.screenshot({ path:`storage/app/qa/content-${name}-${lang}-${width}.png`, fullPage:true });
      }
    }
    assert.deepEqual(errors, []);
    console.log('PASS: bilingual draft preservation, keyboard translation search, and homepage/admin layouts in both languages at three widths.');
  } finally { await browser.close(); execFileSync('php',['tests/profile-browser-fixture.php','cleanup'],{env}); }
})().catch(e => { console.error(e); process.exit(1); });
