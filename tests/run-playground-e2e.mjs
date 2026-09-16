import { spawn } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const releaseVersion = JSON.parse(fs.readFileSync(path.join(root, 'release-plugin', 'patriot-web-solutions', 'payload', 'content.json'), 'utf8')).version;
const archive = path.join(root, 'dist', `patriot-web-solutions-release-${releaseVersion}.zip`);
const bundled = path.join(root, 'tests', 'playground', 'plugin.zip');
if (!fs.existsSync(archive)) throw new Error('Build the release ZIP before clean integration testing.');
fs.copyFileSync(archive, bundled);

const cli = path.join(root, 'node_modules', '@wp-playground', 'cli', 'wp-playground.js');
const env = { ...process.env, NODE_OPTIONS: `${process.env.NODE_OPTIONS || ''} --use-system-ca`.trim() };
const port = process.env.PWS_TEST_PORT || '9412';
env.PWS_TEST_URL = `http://127.0.0.1:${port}`;
const server = spawn(process.execPath, [cli, 'server', '--blueprint=tests/playground', '--blueprint-may-read-adjacent-files', `--port=${port}`, '--wp=6.5', '--php=8.1'], { cwd: root, env, stdio: ['ignore', 'pipe', 'pipe'] });
let output = '';
let settled = false;

const cleanup = () => {
  if (!server.killed) server.kill('SIGINT');
};
process.on('exit', cleanup);
process.on('SIGINT', () => { cleanup(); process.exit(130); });

const timeout = setTimeout(() => {
  if (!settled) {
    console.error(output);
    cleanup();
    process.exit(1);
  }
}, 90000);

async function runE2E() {
  settled = true;
  clearTimeout(timeout);
  const test = spawn(process.execPath, ['tests/e2e.mjs'], { cwd: root, env, stdio: 'inherit' });
  const exitCode = await new Promise((resolve) => test.on('exit', resolve));
  cleanup();
  process.exit(exitCode ?? 1);
}

for (const stream of [server.stdout, server.stderr]) {
  stream.setEncoding('utf8');
  stream.on('data', (chunk) => {
    output += chunk;
    if (!settled && output.includes('Ready! WordPress is running')) runE2E();
  });
}
server.on('exit', (code) => {
  if (!settled) {
    clearTimeout(timeout);
    console.error(output);
    process.exit(code ?? 1);
  }
});
