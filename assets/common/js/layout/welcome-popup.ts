((): void => {
  const dialog = document.querySelector<HTMLElement>('.welcome-popup-dialog');
  if (!dialog) return;

  const snippetId = dialog.dataset.snippetId;
  const storageKey = `welcome-popup-dismissed-${snippetId}`;

  const dismiss = (): void => {
    window.localStorage.setItem(storageKey, '1');
    dialog.classList.remove('show');
  };

  if (!window.localStorage.getItem(storageKey)) {
    dialog.classList.add('show');
  }

  dialog.querySelector('.close-button')?.addEventListener('click', dismiss);
  dialog.querySelector('.close-background')?.addEventListener('click', dismiss);
  dialog.querySelectorAll('a').forEach((link) => link.addEventListener('click', dismiss));
})();
