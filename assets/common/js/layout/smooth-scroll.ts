((): void => {
  const links = document.querySelectorAll<HTMLAnchorElement>('[href*="#"]');
  const offset = 100;

  const anchorHandler = (e: Event, linkHref: string): void => {
    const address = linkHref.split('#');
    const rawPath = address[0];
    const rawAnchor = address[1] ?? '';

    const linkPathname = rawPath.endsWith('/') ? rawPath.slice(0, -1) : rawPath;
    const linkAnchor = rawAnchor.length ? `#${rawAnchor}` : '';

    const windowPathname = window.location.pathname.replace(/\/$/, '');

    if (linkPathname.length && linkPathname !== windowPathname) return;

    e.preventDefault();
    if (!linkAnchor) return;
    window.history.pushState(null, '', linkHref);

    const target = document.querySelector<HTMLElement>(linkAnchor);
    if (target) {
      const offsetPosition = target.getBoundingClientRect().top - offset;
      window.scrollBy({ top: offsetPosition,
        behavior: 'smooth' });
    }
  };

  for (const link of links) {
    const linkHref = link.getAttribute('href');
    if (!linkHref) continue;
    link.addEventListener('click', (e) => anchorHandler(e, linkHref));
  }

  window.addEventListener('load', (e) => {
    const hash = window.location.hash;
    if (hash) anchorHandler(e, hash);
  });
})();
