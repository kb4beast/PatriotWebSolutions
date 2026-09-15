(() => {
  const button = document.querySelector('.pws-menu-toggle');
  const menu = document.querySelector('.pws-nav');
  if (!button || !menu) return;
  button.addEventListener('click', () => {
    const open = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', String(!open));
    menu.classList.toggle('is-open', !open);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && menu.classList.contains('is-open')) {
      button.setAttribute('aria-expanded', 'false');
      menu.classList.remove('is-open');
      button.focus();
    }
  });
})();

