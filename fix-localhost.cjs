const { Client } = require('ssh2');

const conn = new Client();

const SERVER = '144.225.8.129';
const USER = 'root';
const PASS = 'Ayaan_123#@!';
const PROJECT_PATH = '/opt/web-env/site1';

const FILES = [
  'resources/views/admin/ai-agents/index.blade.php',
  'resources/views/admin/erpnext/index.blade.php',
  'resources/views/admin/mautic/index.blade.php',
  'app/Http/Controllers/Admin/ServiceHealthController.php',
  'app/Http/Controllers/Admin/SystemStatusController.php',
  'app/Http/Controllers/Admin/AutomationHubController.php',
  'app/Services/RecommendationService.php',
  'app/Services/AiAgentService.php',
];

function run(conn, cmd) {
  return new Promise((resolve, reject) => {
    conn.exec(cmd, (err, stream) => {
      if (err) return reject(err);
      let out = '';
      let errOut = '';
      stream.on('data', (data) => { out += data; });
      stream.stderr.on('data', (data) => { errOut += data; });
      stream.on('close', () => resolve({ out, errOut }));
    });
  });
}

async function main() {
  await new Promise((resolve, reject) => {
    conn.on('ready', resolve).on('error', reject).connect({ host: SERVER, username: USER, password: PASS });
  });
  console.log('Connected to server.\n');

  for (const file of FILES) {
    const fullPath = `${PROJECT_PATH}/${file}`;
    console.log(`\n========== ${file} ==========`);

    const before = await run(conn, `cd ${PROJECT_PATH} && grep -n "localhost" "${fullPath}" 2>/dev/null || echo "[no localhost references]"`);
    console.log('Before:', before.out.trim());

    await run(conn, `cd ${PROJECT_PATH} && sed -i 's|http://localhost:8080|https://www.onclik2buy.com|g; s|http://localhost:8000|https://www.onclik2buy.com|g; s|http://localhost|https://www.onclik2buy.com|g; s|localhost:8080|https://www.onclik2buy.com|g; s|localhost:8000|https://www.onclik2buy.com|g' "${fullPath}"`);

    const after = await run(conn, `cd ${PROJECT_PATH} && grep -n "localhost" "${fullPath}" 2>/dev/null || echo "[no localhost references]"`);
    console.log('After:', after.out.trim());
  }

  console.log('\n========== Caching config & routes ==========');
  const cache = await run(conn, `cd ${PROJECT_PATH} && php artisan config:cache && php artisan route:cache`);
  console.log(cache.out);
  if (cache.errOut) console.log('stderr:', cache.errOut);

  conn.end();
  console.log('Done.');
}

main().catch(e => { console.error(e); process.exit(1); });
