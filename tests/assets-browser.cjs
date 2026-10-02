const {chromium}=require('C:/Users/Jessie/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const {execFileSync}=require('node:child_process');
const {randomBytes}=require('node:crypto');
const assert=require('node:assert/strict');
const fs=require('node:fs');
(async()=>{
 const env={...process.env,YDA_QA_EMAIL:`qa-${randomBytes(8).toString('hex')}@example.test`,YDA_QA_PASSWORD:randomBytes(18).toString('hex')};
 execFileSync('php',['tests/profile-browser-fixture.php','create'],{env});
 const browser=await chromium.launch({headless:true,channel:'msedge'});let removeUrl;
 const page=await browser.newPage();
 try {
  const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.route('https://tile.openstreetmap.org/**',route=>route.abort());
  fs.mkdirSync('storage/app/qa',{recursive:true});
  await page.setContent('<html><body><h1>Fictional QA homepage illustration</h1><p>Not archival evidence. Removed after testing.</p></body></html>');
  const png=await page.screenshot();
  await page.goto('http://127.0.0.1:8000/login?lang=en');
  await page.locator('[name=email]').fill(env.YDA_QA_EMAIL);await page.locator('[name=password]').fill(env.YDA_QA_PASSWORD);await page.locator('button.primary').click();await page.waitForURL('**/profile');
  await page.goto('http://127.0.0.1:8000/admin/assets?lang=en');
  const add=()=>page.locator('form[action$="/admin/categories"]');
  await add().locator('[name=key]').fill('qa-unsaved');
  await add().locator('[name=name_en]').fill('Unsaved test category');
  await add().locator('[name=name_zh]').fill('未保存测试分类');
  await add().locator('[name=svg]').setInputFiles({name:'qa.svg',mimeType:'image/svg+xml',buffer:Buffer.from('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>')});
  await page.locator('a[lang="zh-Hans"]').click();await page.waitForFunction(()=>document.documentElement.lang==='zh-Hans');
  assert.equal(await add().locator('[name=name_en]').inputValue(),'Unsaved test category');
  assert.equal(await add().locator('[name=svg]').evaluate(el=>el.files[0].name),'qa.svg');
  const upload=page.locator('form[action$="/admin/homepage-images"]');
  await upload.locator('[name=image]').setInputFiles({name:'qa.png',mimeType:'image/png',buffer:png});
  await upload.locator('[name=alt_en]').fill('Fictional QA homepage illustration');await upload.locator('[name=alt_zh]').fill('虚构的首页测试插图');
  await upload.locator('[name=credit]').fill(env.YDA_QA_EMAIL);await upload.locator('[name=permission]').check();
  await upload.locator('button').click();await page.waitForLoadState('networkidle');
  const ownFigure=page.locator('figure').filter({hasText:env.YDA_QA_EMAIL});
  removeUrl=await ownFigure.locator('xpath=following-sibling::form[1]').getAttribute('action');
  for(const lang of ['en','zh-Hans'])for(const width of [390,768,1440]){
   await page.setViewportSize({width,height:900});
   for(const [name,path] of [['admin','/admin/assets'],['home','/'],['atlas','/atlas']]){
    await page.goto('http://127.0.0.1:8000'+path+'?lang='+lang);
    if(name==='atlas'){await page.locator('[data-map-toggle]').click();await page.waitForTimeout(100);}
    const images=page.locator(name==='home'?'img[src*="/homepage-images/"]':'img[src*="/markers/"]');
    await images.first().waitFor();
    await page.waitForFunction(()=>[...document.querySelectorAll('img[src*="/markers/"],img[src*="/homepage-images/"]')].every(img=>img.complete&&img.naturalWidth>0));
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,`${name}/${lang}/${width}`);
    await page.screenshot({path:`storage/app/qa/assets-${name}-${lang}-${width}.png`,fullPage:true});
   }
  }
  assert.deepEqual(errors,[]);
  console.log('PASS: SVG draft preservation, photo publication, category legend images and bilingual layouts at three widths.');
 }finally{
  if(removeUrl){await page.goto('http://127.0.0.1:8000/admin/assets');const form=page.locator(`form[action="${removeUrl}"]`);await form.locator('button').click();await page.waitForLoadState('networkidle');}
  await browser.close();execFileSync('php',['tests/profile-browser-fixture.php','cleanup'],{env});
 }
})().catch(e=>{console.error(e);process.exit(1)});
