/**
 * ساخت اسپرایت SVG خودمیزبان از Tabler Icons.
 *
 * فقط گلیف‌هایی که در build-tools/icons.txt فهرست شده‌اند وارد اسپرایت می‌شوند
 * (الزام فاز ۱ سند PRD: «فقط گلیف‌های استفاده‌شده»). خروجی: public/icons.svg
 *
 * اجرا:  npm run icons
 */
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const outlineDir = join(root, 'node_modules', '@tabler', 'icons', 'icons', 'outline');
const filledDir = join(root, 'node_modules', '@tabler', 'icons', 'icons', 'filled');
const listFile = join(root, 'build-tools', 'icons.txt');
const outFile = join(root, 'public', 'icons.svg');

const names = readFileSync(listFile, 'utf8')
    .split('\n')
    .map((l) => l.trim())
    .filter((l) => l && !l.startsWith('#'));

const missing = [];
const symbols = [];

for (const name of [...new Set(names)].sort()) {
    // چند گلیف (مثل point-filled) فقط در مجموعه‌ی filled وجود دارند.
    const outline = join(outlineDir, `${name}.svg`);
    const filled = join(filledDir, `${name.replace(/-filled$/, '')}.svg`);
    const file = existsSync(outline) ? outline : filled;
    if (!existsSync(file)) {
        missing.push(name);
        continue;
    }
    const isFilled = file === filled;
    // بدنه‌ی مسیرها را از فایل بیرون می‌کشیم و مسیرِ پس‌زمینه‌ی خالیِ Tabler را دور می‌ریزیم.
    const body = readFileSync(file, 'utf8')
        .replace(/^[\s\S]*?<svg[^>]*>/, '')
        .replace(/<\/svg>\s*$/, '')
        .replace(/<path\s+stroke="none"\s+d="M0 0h24v24H0z"\s+fill="none"\s*\/>/g, '')
        .replace(/\s+/g, ' ')
        .trim();

    // سمبل‌های filled باید stroke را خنثی و fill را به currentColor بسپارند.
    const attrs = isFilled ? ' fill="currentColor" stroke="none"' : '';
    symbols.push(`<symbol id="i-${name}" viewBox="0 0 24 24"${attrs}>${body}</symbol>`);
}

if (missing.length) {
    console.error(`✖ آیکون‌های پیدانشده در بسته‌ی Tabler: ${missing.join(', ')}`);
    process.exit(1);
}

// stroke/fill روی خودِ <svg> ریشه می‌نشیند تا همه‌ی سمبل‌ها ارث ببرند و
// رنگ از currentColor بیاید (توکن‌های کیت، بدون مقدار خام).
const sprite =
    `<svg xmlns="http://www.w3.org/2000/svg" style="display:none" ` +
    `fill="none" stroke="currentColor" stroke-width="2" ` +
    `stroke-linecap="round" stroke-linejoin="round">` +
    symbols.join('') +
    `</svg>\n`;

writeFileSync(outFile, sprite, 'utf8');
console.log(`✓ ${symbols.length} آیکون در public/icons.svg نوشته شد (${(sprite.length / 1024).toFixed(1)} KB)`);
