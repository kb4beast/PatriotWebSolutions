import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { chromium } from 'playwright';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const dir = path.join(root, 'brand');
const svg = await fs.readFile(path.join(dir, 'favicon.svg'));
const url = `data:image/svg+xml;base64,${svg.toString('base64')}`;
const browser = await chromium.launch({ headless: true });

try {
  for (const size of [16, 32, 48, 180, 512]) {
    const page = await browser.newPage({ viewport: { width: size, height: size }, deviceScaleFactor: 1 });
    await page.setContent(`<style>html,body{margin:0;width:${size}px;height:${size}px}</style><img alt="" width="${size}" height="${size}" src="${url}">`);
    await page.locator('img').evaluate((image) => image.decode());
    await page.screenshot({ path: path.join(dir, `favicon-ai-patriot-${size}.png`) });
    await page.close();
  }
} finally {
  await browser.close();
}
