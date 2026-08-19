<?php
/**
 * Camada de auditoria e anti-bot do Hub Estudantil.
 * Coleta apenas sinais expostos pelo navegador/HTTP. Não tenta acessar arquivos,
 * serial do equipamento, MAC ou nome da rede Wi-Fi (SSID), que não são expostos
 * a páginas web comuns por motivos de segurança e privacidade.
 */

function hub_security_client_ip(): string
{
    // Em Vercel/proxies confiáveis, X-Forwarded-For contém o IP original primeiro.
    $raw = (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    $candidate = trim(explode(',', $raw)[0]);
    return filter_var($candidate, FILTER_VALIDATE_IP) ? substr($candidate, 0, 45) : '0.0.0.0';
}

function hub_security_client_data(): array
{
    $raw = (string)($_POST['client_security'] ?? '');
    if ($raw === '' || strlen($raw) > 20000) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function hub_security_sanitize_scalar($value, int $max = 255): ?string
{
    if ($value === null || is_array($value) || is_object($value)) return null;
    return mb_substr(trim((string)$value), 0, $max, 'UTF-8');
}

function hub_security_ensure_schema(PDO $pdo): void
{
    static $done = false;
    if ($done) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS security_events (
            id BIGSERIAL PRIMARY KEY,
            user_id INT NULL REFERENCES users(id) ON DELETE SET NULL,
            event_type VARCHAR(40) NOT NULL,
            identifier VARCHAR(120),
            ip VARCHAR(45),
            user_agent TEXT,
            client_data JSONB,
            created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_security_events_ip_time ON security_events(ip, created_at DESC)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_security_events_identifier_time ON security_events(identifier, created_at DESC)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_security_events_type_time ON security_events(event_type, created_at DESC)");

        $columns = [
            'user_agent TEXT',
            'browser VARCHAR(160)',
            'platform VARCHAR(120)',
            'device VARCHAR(120)',
            'battery_percent SMALLINT',
            'screen_width INT',
            'screen_height INT',
            'device_memory_gb NUMERIC(6,2)',
            'storage_quota_mb BIGINT',
            'storage_usage_mb BIGINT',
            'network_type VARCHAR(40)',
            'connection_effective_type VARCHAR(40)',
            'automation_detected BOOLEAN',
            'client_data JSONB'
        ];
        foreach ($columns as $column) {
            $pdo->exec('ALTER TABLE user_logins ADD COLUMN IF NOT EXISTS ' . $column);
        }
    } catch (Throwable $e) {
        // Auditoria nunca deve derrubar a autenticação principal.
    }
    $done = true;
}

function hub_security_identifier(string $identifier): string
{
    return mb_substr(mb_strtolower(trim($identifier), 'UTF-8'), 0, 120, 'UTF-8');
}

function hub_security_record_event(PDO $pdo, string $type, string $identifier = '', ?int $userId = null): void
{
    try {
        hub_security_ensure_schema($pdo);
        $data = hub_security_client_data();
        $stmt = $pdo->prepare('INSERT INTO security_events (user_id,event_type,identifier,ip,user_agent,client_data)
            VALUES (:uid,:type,:identifier,:ip,:ua,CAST(:data AS JSONB))');
        $stmt->execute([
            ':uid' => $userId,
            ':type' => mb_substr($type, 0, 40, 'UTF-8'),
            ':identifier' => hub_security_identifier($identifier),
            ':ip' => hub_security_client_ip(),
            ':ua' => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 2000, 'UTF-8'),
            ':data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
        ]);
    } catch (Throwable $e) {}
}

function hub_security_is_rate_limited(PDO $pdo, string $identifier = ''): bool
{
    try {
        hub_security_ensure_schema($pdo);
        $ip = hub_security_client_ip();
        $id = hub_security_identifier($identifier);

        // Protege contra rajadas sem punir excessivamente redes escolares compartilhadas.
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM security_events
            WHERE ip=:ip AND created_at > NOW() - INTERVAL '10 minutes'
              AND event_type IN ('login_failed','login_rate_limited','register_failed','register_rate_limited')");
        $stmt->execute([':ip'=>$ip]);
        if ((int)$stmt->fetchColumn() >= 20) return true;

        if ($id !== '') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM security_events
                WHERE identifier=:identifier AND created_at > NOW() - INTERVAL '10 minutes'
                  AND event_type IN ('login_failed','login_rate_limited','register_failed','register_rate_limited')");
            $stmt->execute([':identifier'=>$id]);
            if ((int)$stmt->fetchColumn() >= 8) return true;
        }
        return false;
    } catch (Throwable $e) {
        return false;
    }
}

function hub_turnstile_enabled(): bool
{
    return trim((string)getenv('TURNSTILE_SITE_KEY')) !== '' && trim((string)getenv('TURNSTILE_SECRET_KEY')) !== '';
}

function hub_turnstile_site_key(): string
{
    return trim((string)getenv('TURNSTILE_SITE_KEY'));
}

function hub_turnstile_verify(): bool
{
    if (!hub_turnstile_enabled()) return true;
    $token = trim((string)($_POST['cf-turnstile-response'] ?? ''));
    if ($token === '') return false;

    $payload = http_build_query([
        'secret' => trim((string)getenv('TURNSTILE_SECRET_KEY')),
        'response' => $token,
        'remoteip' => hub_security_client_ip(),
    ]);

    $response = false;
    if (function_exists('curl_init')) {
        $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http'=>[
            'method'=>'POST', 'timeout'=>5,
            'header'=>"Content-Type: application/x-www-form-urlencoded\r\n",
            'content'=>$payload,
        ]]);
        $response = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $ctx);
    }

    if (!is_string($response) || $response === '') return false;
    $decoded = json_decode($response, true);
    return is_array($decoded) && ($decoded['success'] ?? false) === true;
}

function hub_security_telemetry_script(string $formSelector = 'form'): string
{
    $selector = json_encode($formSelector, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return <<<HTML
<script>
(async function(){
  const form = document.querySelector($selector);
  if (!form) return;
  let input = form.querySelector('input[name="client_security"]');
  if (!input) {
    input = document.createElement('input');
    input.type = 'hidden'; input.name = 'client_security';
    form.appendChild(input);
  }

  async function collectSecuritySignals(){
    const nav = navigator;
    const scr = window.screen || {};
    const data = {
      collectedAt: new Date().toISOString(),
      userAgent: nav.userAgent || null,
      platform: nav.userAgentData?.platform || nav.platform || null,
      mobile: nav.userAgentData?.mobile ?? null,
      brands: nav.userAgentData?.brands || null,
      language: nav.language || null,
      languages: nav.languages || null,
      logicalProcessors: nav.hardwareConcurrency || null,
      deviceMemoryGB: nav.deviceMemory || null,
      maxTouchPoints: nav.maxTouchPoints || 0,
      webdriver: nav.webdriver === true,
      cookiesEnabled: nav.cookieEnabled,
      online: nav.onLine,
      screen: {
        width: scr.width || null, height: scr.height || null,
        availWidth: scr.availWidth || null, availHeight: scr.availHeight || null,
        colorDepth: scr.colorDepth || null, pixelDepth: scr.pixelDepth || null,
        pixelRatio: window.devicePixelRatio || 1
      },
      viewport: {width: window.innerWidth || null, height: window.innerHeight || null},
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || null,
      network: null,
      battery: null,
      storage: null,
      wifiName: null
    };

    const conn = nav.connection || nav.mozConnection || nav.webkitConnection;
    if (conn) data.network = {
      type: conn.type || null,
      effectiveType: conn.effectiveType || null,
      downlinkMbps: conn.downlink ?? null,
      rttMs: conn.rtt ?? null,
      saveData: conn.saveData ?? null
    };

    try {
      if (nav.storage?.estimate) {
        const est = await nav.storage.estimate();
        data.storage = {
          quotaBytes: est.quota ?? null,
          usageBytes: est.usage ?? null,
          quotaMB: est.quota ? Math.round(est.quota / 1048576) : null,
          usageMB: est.usage ? Math.round(est.usage / 1048576) : 0
        };
      }
    } catch(e) {}

    try {
      if (nav.getBattery) {
        const b = await nav.getBattery();
        data.battery = {
          percent: Number.isFinite(b.level) ? Math.round(b.level * 100) : null,
          charging: !!b.charging,
          chargingTime: Number.isFinite(b.chargingTime) ? b.chargingTime : null,
          dischargingTime: Number.isFinite(b.dischargingTime) ? b.dischargingTime : null
        };
      }
    } catch(e) {}

    // Nome/SSID do Wi-Fi não é disponibilizado para páginas web normais.
    input.value = JSON.stringify(data);
  }

  await collectSecuritySignals();
  form.addEventListener('submit', async function(ev){
    if (form.dataset.securityReady === '1') return;
    ev.preventDefault();
    await collectSecuritySignals();
    form.dataset.securityReady = '1';
    form.requestSubmit();
  });
})();
</script>
HTML;
}
