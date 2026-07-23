((): void => {
  const carousels = document.querySelectorAll<HTMLElement>('.carousel');

  function getIndex(carousel: HTMLElement): number {
    const carouselWidth = carousel.clientWidth;
    const wrapper = carousel.querySelector<HTMLElement>('.carousel-wrapper')!;
    const wrapperPaddingLeft = parseInt(window.getComputedStyle(wrapper).paddingLeft);
    const gap = parseInt(window.getComputedStyle(wrapper).gap) || 0;
    const wrapperScroll = wrapper.scrollLeft;
    const slideWidth = (wrapper.children[0] as HTMLElement).clientWidth;
    const swg = slideWidth + gap;
    const center = carouselWidth / 2 - 20;
    const totalCarouselWidth = wrapper.children.length * swg - gap;

    if (wrapperScroll < 20) return 0;
    if (wrapperScroll > totalCarouselWidth - carouselWidth - 20) return wrapper.children.length - 1;

    return Math.round((wrapperScroll + center - wrapperPaddingLeft - 0.5 * slideWidth) / swg);
  }

  function disableArrowsOnEnds(carousel: HTMLElement, leftArrow: HTMLElement, rightArrow: HTMLElement, wrapper: HTMLElement): void {
    const index = getIndex(carousel);
    leftArrow.classList.toggle('disabled', index === 0);
    rightArrow.classList.toggle('disabled', index === wrapper.children.length - 1);
  }

  function alignCarousel(carousel: HTMLElement, index: number | null = null): void {
    const wrapper = carousel.querySelector<HTMLElement>('.carousel-wrapper')!;
    const wrapperPaddingLeft = parseInt(window.getComputedStyle(wrapper).paddingLeft);
    const gap = parseInt(window.getComputedStyle(wrapper).gap);
    const carouselWidth = carousel.clientWidth;
    const slideWidth = (wrapper.children[0] as HTMLElement).clientWidth;
    const swg = slideWidth + gap;
    const center = carouselWidth / 2;

    if (index === null) index = getIndex(carousel);

    wrapper.scrollTo({
      left: wrapperPaddingLeft + index * swg + 0.5 * swg - center - 0.5 * gap,
      behavior: 'smooth',
    });
  }

  function setAutoplay(carousel: HTMLElement, interval: number): ReturnType<typeof setInterval> | null {
    if (interval === 0) return null;
    let index = getIndex(carousel);
    const items = carousel.querySelector<HTMLElement>('.carousel-wrapper')!.children.length;
    return setInterval(() => {
      if (index < items - 1) {
        alignCarousel(carousel, index + 1);
        index++;
      } else {
        alignCarousel(carousel, 0);
        index = 0;
      }
    }, interval * 1000);
  }

  carousels.forEach((carousel) => {
    const wrapper = carousel.querySelector<HTMLElement>('.carousel-wrapper');
    const navigation = carousel.querySelector('.carousel-navigation');
    if (!wrapper || !navigation) return;

    let position = wrapper.scrollLeft;
    let mouseStart: number | null = null;
    const autoplayInterval = parseInt(carousel.dataset.autoplay ?? '0') || 0;
    let interval = setAutoplay(carousel, autoplayInterval);

    const leftArrow = carousel.querySelector<HTMLElement>('.arrow.left')!;
    const rightArrow = carousel.querySelector<HTMLElement>('.arrow.right')!;
    const dots = carousel.querySelector<HTMLElement>('.dots');

    wrapper.addEventListener('mousedown', (event) => {
      mouseStart = event.clientX;
      position = wrapper.scrollLeft;
      if (interval) clearInterval(interval);
    });

    wrapper.addEventListener('mouseenter', () => {
      if (interval) clearInterval(interval);
    });

    wrapper.addEventListener('mouseleave', () => {
      if (mouseStart !== null) return;
      interval = setAutoplay(carousel, autoplayInterval);
    });

    window.addEventListener('mouseup', () => {
      if (mouseStart === null) return;
      mouseStart = null;
      alignCarousel(carousel);
      setTimeout(() => wrapper.classList.remove('dragging'), 300);
      interval = setAutoplay(carousel, autoplayInterval);
    });

    window.addEventListener('mousemove', (event) => {
      if (mouseStart === null) return;
      wrapper.classList.add('dragging');
      wrapper.scrollLeft = position - (event.clientX - mouseStart);
    });

    disableArrowsOnEnds(carousel, leftArrow, rightArrow, wrapper);
    wrapper.addEventListener('scroll', () => disableArrowsOnEnds(carousel, leftArrow, rightArrow, wrapper));

    leftArrow.addEventListener('click', () => {
      const index = getIndex(carousel);
      if (index > 0) alignCarousel(carousel, index - 1);
    });

    rightArrow.addEventListener('click', () => {
      const index = getIndex(carousel);
      if (index < wrapper.children.length - 1) alignCarousel(carousel, index + 1);
    });

    if (dots) {
      for (let i = 0; i < wrapper.children.length; i++) {
        const dot = document.createElement('div');
        dot.classList.add('dot');
        if (i === getIndex(carousel)) dot.classList.add('active');
        dots.appendChild(dot);
        dot.addEventListener('click', () => alignCarousel(carousel, i));
      }

      wrapper.addEventListener('scroll', () => {
        const index = getIndex(carousel);
        carousel.querySelectorAll('.dot').forEach((dot, i) => {
          dot.classList.toggle('active', i === index);
        });
      });
    }
  });
})();
