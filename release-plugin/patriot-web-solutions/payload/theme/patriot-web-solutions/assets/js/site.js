(() => {
const init = () => {
  const button = document.querySelector('.pws-menu-toggle');
  const menu = document.querySelector('.pws-nav');
  if (!button || !menu || button.dataset.pwsReady) return false;
  button.dataset.pwsReady = '1';
  const setOpen = (open) => {
    button.setAttribute('aria-expanded', String(open));
    menu.classList.toggle('is-open', open);
  };
  button.addEventListener('click', () => setOpen(button.getAttribute('aria-expanded') !== 'true'));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && menu.classList.contains('is-open')) {
      setOpen(false);
      button.focus();
    }
  });
  document.addEventListener('click', (event) => {
    if (menu.classList.contains('is-open') && !menu.contains(event.target) && !button.contains(event.target)) setOpen(false);
  });
  // Mark the current page in the primary navigation for assistive technology and the accent state.
  const here = location.pathname.replace(/\/+$/, '') || '/';
  menu.querySelectorAll('.pws-nav__list a').forEach((a) => {
    const path = new URL(a.href, location.origin).pathname.replace(/\/+$/, '') || '/';
    if (path === here) a.setAttribute('aria-current', 'page');
  });
  return true;
};
if (!init()) {
  // The header may render after this script (deferred or client-rendered previews): wait for it.
  const observer = new MutationObserver(() => { if (init()) observer.disconnect(); });
  observer.observe(document.documentElement, { childList: true, subtree: true });
}
})();
