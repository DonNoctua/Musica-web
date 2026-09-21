<?php
declare(strict_types=1);

session_start();

/* Cambia esta contraseña antes de publicar el sitio. */
const ADMIN_PASS = 'cambia-esta-clave';
const CONTENT_FILE = __DIR__ . '/portfolio-content.json';

$defaults = [
    'name' => 'Lucía Navarro',
    'role' => 'Diseñadora & desarrolladora digital',
    'location' => 'Madrid, ES',
    'availability' => 'Disponible para proyectos selectos',
    'intro' => 'Construyo experiencias digitales claras, útiles y con personalidad.',
    'about' => 'Combino estrategia, diseño y código para transformar ideas complejas en productos que se sienten simples.',
    'email' => 'hola@lucianavarro.dev',
    'accent' => '#9b8cff',
    'projects' => [
        ['title' => 'Aster Journal', 'type' => 'Editorial · 2024', 'description' => 'Una plataforma de lectura lenta para historias que merecen tiempo.', 'url' => '#'],
        ['title' => 'Forma Studio', 'type' => 'Identidad · 2024', 'description' => 'Sistema visual y sitio de lanzamiento para un estudio de arquitectura.', 'url' => '#'],
        ['title' => 'Marea', 'type' => 'Producto digital · 2023', 'description' => 'Una nueva manera de descubrir escapadas junto al mar.', 'url' => '#'],
    ],
];

function loadContent(array $defaults): array
{
    if (!is_file(CONTENT_FILE)) {
        return $defaults;
    }
    $saved = json_decode((string) file_get_contents(CONTENT_FILE), true);
    return is_array($saved) ? array_replace($defaults, $saved) : $defaults;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$notice = '';
$isAdmin = !empty($_SESSION['is_admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], is_string($csrf) ? $csrf : '')) {
        http_response_code(403);
        exit('Solicitud no válida. Recarga la página e inténtalo de nuevo.');
    }

    if ($action === 'login') {
        $password = $_POST['password'] ?? '';
        if (is_string($password) && hash_equals(ADMIN_PASS, $password)) {
            session_regenerate_id(true);
            $_SESSION['is_admin'] = true;
            header('Location: #panel');
            exit;
        }
        $notice = 'La contraseña no es correcta.';
    }

    if ($action === 'logout' && $isAdmin) {
        $_SESSION = [];
        session_destroy();
        session_start();
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        header('Location: #panel');
        exit;
    }

    if ($action === 'save' && $isAdmin) {
        $content = loadContent($defaults);
        foreach (['name', 'role', 'location', 'availability', 'intro', 'about', 'email', 'accent'] as $field) {
            if (isset($_POST[$field]) && is_string($_POST[$field])) {
                $content[$field] = trim($_POST[$field]);
            }
        }
        $titles = $_POST['project_title'] ?? [];
        $types = $_POST['project_type'] ?? [];
        $descriptions = $_POST['project_description'] ?? [];
        $urls = $_POST['project_url'] ?? [];
        $content['projects'] = [];
        foreach ($titles as $i => $title) {
            if (!is_string($title) || trim($title) === '') continue;
            $content['projects'][] = [
                'title' => trim($title), 'type' => trim((string) ($types[$i] ?? '')), 'description' => trim((string) ($descriptions[$i] ?? '')), 'url' => trim((string) ($urls[$i] ?? '#')),
            ];
        }
        if (!$content['projects']) $content['projects'] = $defaults['projects'];
        $written = file_put_contents(CONTENT_FILE, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        $notice = $written === false ? 'No se pudo guardar. Comprueba los permisos de escritura del servidor.' : 'Cambios guardados correctamente.';
    }
}

$content = loadContent($defaults);
$projects = is_array($content['projects'] ?? null) ? $content['projects'] : $defaults['projects'];
?>
<!doctype html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Portafolio de <?= e($content['name']) ?>, <?= e($content['role']) ?>.">
  <title><?= e($content['name']) ?> — Portfolio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body style="--accent: <?= e($content['accent']) ?>">
  <div class="noise" aria-hidden="true"></div>
  <div class="app-shell">
    <aside class="sidebar" id="sidebar">
      <div class="brand"><span class="brand-mark">ln</span><span>portfolio</span></div>
      <div class="explorer-title"><span>EXPLORADOR</span><button class="icon-button sidebar-close" aria-label="Cerrar menú">×</button></div>
      <nav class="file-tree" aria-label="Navegación principal">
        <a class="folder" href="#inicio"><span>⌄</span> portfolio</a>
        <a class="file active" href="#inicio"><i></i> index.php</a>
        <a class="file" href="#proyectos"><i></i> work.json</a>
        <a class="file" href="#sobre-mi"><i></i> about.md</a>
        <a class="file" href="#contacto"><i></i> contact.js</a>
        <a class="file config" href="#panel"><i></i> panel.config</a>
      </nav>
      <div class="sidebar-footer"><span class="status-dot"></span> <?= e($content['availability']) ?></div>
    </aside>

    <main>
      <header class="topbar">
        <button class="icon-button menu-button" aria-label="Abrir menú" aria-controls="sidebar">☰</button>
        <div class="breadcrumb"><span>portfolio</span><b>/</b><span id="current-file">index.php</span></div>
        <div class="topbar-actions"><button class="theme-toggle" type="button" aria-label="Cambiar tema"><span>◐</span> theme</button><span class="window-dots"><i></i><i></i><i></i></span></div>
      </header>

      <section class="hero section" id="inicio">
        <p class="eyebrow reveal">// <?= e($content['role']) ?></p>
        <div class="hero-grid">
          <div>
            <h1 class="reveal">Hola, soy<br><em><?= e($content['name']) ?>.</em></h1>
            <p class="intro reveal"><?= e($content['intro']) ?></p>
            <div class="hero-actions reveal"><a class="button" href="#proyectos">Ver proyectos <span>↘</span></a><a class="text-link" href="mailto:<?= e($content['email']) ?>">Escríbeme <span>→</span></a></div>
          </div>
          <div class="terminal reveal" aria-label="Terminal de presentación"><div class="terminal-bar"><span></span><span></span><span></span><b>terminal — zsh</b></div><div class="terminal-content"><p><span class="prompt">~</span> whoami</p><p class="terminal-output" data-typing="<?= e($content['role']) ?>"></p><p><span class="prompt">~</span> location</p><p class="terminal-output muted" data-typing="<?= e($content['location']) ?>"></p><p><span class="prompt">~</span><span class="cursor"></span></p></div></div>
        </div>
        <div class="scroll-hint">SCROLL PARA EXPLORAR <span>↓</span></div>
      </section>

      <section class="section work-section" id="proyectos">
        <div class="section-heading reveal"><p class="eyebrow">01 — selected_work</p><h2>Proyectos<br>seleccionados.</h2><span><?= str_pad((string) count($projects), 2, '0', STR_PAD_LEFT) ?> proyectos</span></div>
        <div class="project-list">
          <?php foreach ($projects as $index => $project): ?>
          <a class="project-card reveal" href="<?= e((string) ($project['url'] ?? '#')) ?>" <?= (($project['url'] ?? '#') !== '#') ? 'target="_blank" rel="noreferrer"' : '' ?>><span class="project-number">0<?= $index + 1 ?></span><div><p class="project-meta"><?= e((string) ($project['type'] ?? 'Proyecto')) ?></p><h3><?= e((string) ($project['title'] ?? 'Sin título')) ?></h3><p class="project-description"><?= e((string) ($project['description'] ?? '')) ?></p></div><span class="project-arrow">↗</span></a>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="section about-section" id="sobre-mi"><p class="eyebrow reveal">02 — about.md</p><div class="about-grid"><h2 class="reveal">Diseño con intención.<br><em>Código con criterio.</em></h2><div class="reveal"><p><?= e($content['about']) ?></p><ul class="skills"><li>Dirección de arte</li><li>Diseño de producto</li><li>Front-end creativo</li></ul></div></div></section>
      <section class="section contact-section" id="contacto"><p class="eyebrow reveal">03 — contact.js</p><div class="contact-row reveal"><h2>¿Tienes algo<br>en mente?</h2><a href="mailto:<?= e($content['email']) ?>" class="email-link"><?= e($content['email']) ?> <span>↗</span></a></div></section>

      <section class="section panel-section" id="panel">
        <p class="eyebrow reveal">04 — panel.config</p>
        <div class="panel-card reveal">
          <?php if (!$isAdmin): ?>
            <div class="panel-copy"><span class="lock-icon">⌘</span><h2>Área privada.</h2><p>Inicia sesión para editar el contenido y la apariencia del portafolio.</p></div>
            <form class="login-form" method="post" action="#panel"><input type="hidden" name="action" value="login"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><label for="password">Contraseña de administrador</label><div class="input-row"><input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••"><button class="button" type="submit">Entrar <span>→</span></button></div><?php if ($notice): ?><p class="form-notice error"><?= e($notice) ?></p><?php endif; ?></form>
          <?php else: ?>
            <div class="panel-header"><div><span class="admin-label"><i></i> ADMINISTRADOR</span><h2>Panel de control.</h2><p>Edita los campos y guarda los cambios en tu servidor.</p></div><form method="post" action="#panel"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><button class="logout" type="submit">Cerrar sesión ↗</button></form></div>
            <form class="settings-form" method="post" action="#panel"><input type="hidden" name="action" value="save"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
              <div class="form-grid"><label>Nombre<input name="name" value="<?= e($content['name']) ?>" required></label><label>Rol<input name="role" value="<?= e($content['role']) ?>" required></label><label>Ubicación<input name="location" value="<?= e($content['location']) ?>"></label><label>Disponibilidad<input name="availability" value="<?= e($content['availability']) ?>"></label><label>Correo<input name="email" type="email" value="<?= e($content['email']) ?>" required></label><label>Color de acento<input name="accent" type="color" value="<?= e($content['accent']) ?>"></label></div>
              <label>Introducción<textarea name="intro" rows="2"><?= e($content['intro']) ?></textarea></label><label>Sobre mí<textarea name="about" rows="3"><?= e($content['about']) ?></textarea></label>
              <fieldset><legend>Proyectos</legend><?php foreach ($projects as $project): ?><div class="project-fields"><input name="project_title[]" value="<?= e((string) $project['title']) ?>" placeholder="Título"><input name="project_type[]" value="<?= e((string) $project['type']) ?>" placeholder="Categoría"><input name="project_url[]" value="<?= e((string) $project['url']) ?>" placeholder="URL"><textarea name="project_description[]" rows="2" placeholder="Descripción"><?= e((string) $project['description']) ?></textarea></div><?php endforeach; ?></fieldset>
              <div class="save-row"><button class="button" type="submit">Guardar cambios <span>↗</span></button><?php if ($notice): ?><p class="form-notice"><?= e($notice) ?></p><?php endif; ?></div>
            </form>
          <?php endif; ?>
        </div>
      </section>
      <footer><span>© <?= date('Y') ?> <?= e($content['name']) ?></span><span>Diseñado y desarrollado con intención.</span></footer>
    </main>
  </div>
  <script src="script.js" defer></script>
</body>
</html>
