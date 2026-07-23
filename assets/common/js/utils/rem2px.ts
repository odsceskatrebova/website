export function rem2px(rem: number): number {
  return rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
}
