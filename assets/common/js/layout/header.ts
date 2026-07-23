import { rem2px } from '@common/js/utils/rem2px';

((): void => {
  const header = document.querySelector('header');
  const menuToggle = header?.querySelector('.menu-toggle');
  const headerMenuWrapper = document.querySelector('.header-menu-wrapper');
  const headerBackground = document.querySelector('.header-background');

  if (!header || !menuToggle || !headerMenuWrapper || !headerBackground) return;

  const onScroll = (): void => {
    header.classList.toggle('fixed', window.scrollY > rem2px(2));
    headerBackground.classList.toggle('fixed', window.scrollY > rem2px(2));
  };

  const onMenuToggle = (): void => {
    const open = headerMenuWrapper.classList.toggle('show');
    header.classList.toggle('menu-open', open);
    menuToggle.classList.toggle('opened');
  };

  headerMenuWrapper.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      headerMenuWrapper.classList.remove('show');
      header.classList.remove('menu-open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });

  document.addEventListener('scroll', onScroll, { passive: true });
  menuToggle.addEventListener('click', onMenuToggle);

  onScroll();
})();
