import { rem2px } from '@common/js/utils/rem2px';

((): void => {
  const edgeRightCols = document.querySelectorAll<HTMLElement>('.container .row .edge-right');
  const edgeRightColsTablet = document.querySelectorAll<HTMLElement>('.container .row .edge-right-tablet');
  const edgeRightColsDesktop = document.querySelectorAll<HTMLElement>('.container .row .edge-right-desktop');

  const edgeLeftCols = document.querySelectorAll<HTMLElement>('.container .row .edge-left');
  const edgeLeftColsTablet = document.querySelectorAll<HTMLElement>('.container .row .edge-left-tablet');
  const edgeLeftColsDesktop = document.querySelectorAll<HTMLElement>('.container .row .edge-left-desktop');

  const edgeRightCol = (col: HTMLElement): void => {
    col.style.marginRight = '0px';
    const windowHalfWidth = document.documentElement.clientWidth / 2;
    const maxWidth = Math.min(document.documentElement.clientWidth, rem2px(168));
    const colRect = col.getBoundingClientRect();
    const right = maxWidth / 2 - (colRect.right - windowHalfWidth);
    col.style.marginRight = -right + 'px';
  };

  const edgeLeftCol = (col: HTMLElement): void => {
    col.style.marginLeft = '0px';
    const maxWidth = Math.min(document.documentElement.clientWidth, rem2px(168));
    const leftOffset = (document.documentElement.clientWidth - maxWidth) / 2;
    const colRect = col.getBoundingClientRect();
    const left = colRect.left - leftOffset;
    col.style.marginLeft = -left + 'px';
  };

  const tabletBreakpoint = rem2px(55);
  const desktopBreakpoint = rem2px(73);

  const runMargins = (): void => {
    edgeRightCols.forEach(edgeRightCol);
    edgeLeftCols.forEach(edgeLeftCol);

    if (document.documentElement.clientWidth >= tabletBreakpoint) {
      edgeRightColsTablet.forEach(edgeRightCol);
      edgeLeftColsTablet.forEach(edgeLeftCol);
    } else {
      edgeRightColsTablet.forEach((col) => { col.style.marginRight = '0'; });
      edgeLeftColsTablet.forEach((col) => { col.style.marginLeft = '0'; });
    }

    if (document.documentElement.clientWidth >= desktopBreakpoint) {
      edgeRightColsDesktop.forEach(edgeRightCol);
      edgeLeftColsDesktop.forEach(edgeLeftCol);
    } else {
      edgeRightColsDesktop.forEach((col) => { col.style.marginRight = '0'; });
      edgeLeftColsDesktop.forEach((col) => { col.style.marginLeft = '0'; });
    }
  };

  runMargins();
  window.addEventListener('resize', runMargins);
})();
