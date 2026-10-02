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
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', e => errors.push(e.message));
    await page.route('https://tile.openstreetmap.org/**', route => route.abort());
    await page.goto('http://127.0.0.1:8000/login?lang=en');
    await page.locator('[name=email]').fill(env.YDA_QA_EMAIL);
    await page.locator('[name=password]').fill(env.YDA_QA_PASSWORD);
    await page.locator('button.primary').click();
    await page.waitForURL('**/profile');
    await page.goto('http://127.0.0.1:8000/observations/create?lang=en');
    await page.locator('[name=name_en]').fill('QA research citation');
    await page.locator('[name=name_zh]').fill('研究引用测试');
    await page.locator('[name=description_en]').fill('Fictional research test, removed after verification.');
    await page.locator('[name=description_zh]').fill('虚构研究测试，验证后删除。');
    await page.locator('button.primary').click();
    await page.waitForURL(/observations\/(?!create)/);
    const siteUrl = await page.locator('a[href*="/sites/"]').getAttribute('href');
    fs.mkdirSync('storage/app/qa', { recursive: true });
    for (const lang of ['en', 'zh-Hans']) {
      for (const width of [390, 768, 1440]) {
        await page.setViewportSize({ width, height: 900 });
        for (const [name, url] of [['atlas', 'http://127.0.0.1:8000/atlas?search=QA%20research%20citation&lang=' + lang], ['site', siteUrl + '?lang=' + lang]]) {
          await page.goto(url);
          const summary = page.locator('details > summary').last();
          await summary.focus();
          await page.keyboard.press('Enter');
          assert.equal(await summary.evaluate(el => el.parentElement.open), true);
          assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1), false, `${name}/${lang}/${width}`);
          await page.screenshot({ path: `storage/app/qa/research-${name}-${lang}-${width}.png`, fullPage: true });
          const links = name === 'atlas' ? page.locator('a[href*="/atlas/export/"]') : page.locator('a[href*="/citations/"]');
          for (let i = 0; width === 390 && i < await links.count(); i++) {
            const downloadEvent = page.waitForEvent('download');
            await links.nth(i).click();
            const download = await downloadEvent;
            const text = fs.readFileSync(await download.path(), 'utf8');
            assert.ok(text.includes(name === 'atlas' || lang === 'en' ? 'QA research citation' : '研究引用测试'));
            if (download.suggestedFilename().endsWith('.geojson')) assert.equal(JSON.parse(text).features.length, 1);
          }
        }
      }
    }
    assert.deepEqual(errors, []);
    console.log('PASS: filtered CSV/GeoJSON and citation downloads, keyboard disclosure, both languages at three viewport widths.');
  } finally {
    await browser.close();
    execFileSync('php', ['tests/profile-browser-fixture.php', 'cleanup'], { env });
  }
})().catch(error => { console.error(error); process.exit(1); });
