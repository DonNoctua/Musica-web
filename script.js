(() => {
  const root = document.documentElement;
  const sidebar = document.querySelector('.sidebar');
  const menuButton = document.querySelector('.menu-button');
  const closeButton = document.querySelector('.sidebar-close');
  const themeButton = document.querySelector('.theme-toggle');

  const savedTheme = localStorage.getItem('portfolio-theme');
  if (savedTheme) root.dataset.theme = savedTheme;

  themeButton?.addEventListener('click', () => {
    const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = nextTheme;
    localStorage.setItem('portfolio-theme', nextTheme);
  });

  const toggleMenu = (open) => sidebar?.classList.toggle('is-open', open);
  menuButton?.addEventListener('click', () => toggleMenu(true));
  closeButton?.addEventListener('click', () => toggleMenu(false));
  document.querySelectorAll('.file-tree a').forEach((link) => link.addEventListener('click', () => toggleMenu(false)));

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => { if (entry.isIntersecting) entry.target.classList.add('is-visible'); });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach((element) => observer.observe(element));

  const fileMap = { inicio: 'index.php', proyectos: 'work.json', 'sobre-mi': 'about.md', contacto: 'contact.js', panel: 'panel.config' };
  const currentFile = document.querySelector('#current-file');
  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      const id = entry.target.id;
      currentFile.textContent = fileMap[id] || 'index.php';
      document.querySelectorAll('.file-tree a').forEach((link) => link.classList.toggle('active', link.getAttribute('href') === `#${id}`));
    });
  }, { rootMargin: '-35% 0px -55% 0px' });
  Object.keys(fileMap).forEach((id) => { const section = document.getElementById(id); if (section) sectionObserver.observe(section); });

  const typeText = (element, text, delay = 24) => {
    let index = 0;
    const write = () => { element.textContent = text.slice(0, index++); if (index <= text.length) setTimeout(write, delay); };
    write();
  };
  const terminalObserver = new IntersectionObserver((entries, obs) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.querySelectorAll('[data-typing]').forEach((node, index) => setTimeout(() => typeText(node, node.dataset.typing), index * 550));
      obs.unobserve(entry.target);
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.terminal').forEach((terminal) => terminalObserver.observe(terminal));
})();
