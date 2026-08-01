// odsct mobile menu — toggles the full-viewport nav overlay
// (assets/odsct/styles/layout/header.scss) from the hamburger button in
// header.html.twig. Bespoke to this webspace: the generic
// assets/common/js/layout/header.ts targets different markup (a separate
// .header-background element, a .show/.opened toggle pair) and has no
// Escape/scroll-lock handling, so it doesn't fit the odsct markup.

((): void => {
  const header = document.querySelector<HTMLElement>('.site-header');
  const toggle = header?.querySelector<HTMLButtonElement>('.site-header__menu-toggle');
  const menu = header?.querySelector<HTMLElement>('.site-header__menu');

  if (!header || !toggle || !menu) return;

  const isOpen = (): boolean => toggle.getAttribute('aria-expanded') === 'true';

  const setOpen = (open: boolean): void => {
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    menu.classList.toggle('site-header__menu--open', open);
    document.body.classList.toggle('site-header-menu-open', open);
  };

  toggle.addEventListener('click', (): void => {
    setOpen(!isOpen());
  });

  menu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', (): void => setOpen(false));
  });

  document.addEventListener('keydown', (event: KeyboardEvent): void => {
    if (event.key === 'Escape' && isOpen()) {
      setOpen(false);
    }
  });
})();
