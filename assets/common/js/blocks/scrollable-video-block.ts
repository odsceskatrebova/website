((): void => {
  const videoBlocks = document.querySelectorAll<HTMLElement>('.scrollable-video-block');

  const moveVideo = (video: HTMLVideoElement, block: HTMLElement): void => {
    const progressBar = block.querySelector<HTMLElement>('.video-debug-progress__bar');
    const startThreshold = block.querySelector<HTMLElement>('.video-threshold.start');
    const endThreshold = block.querySelector<HTMLElement>('.video-threshold.end');

    if (!endThreshold || !video.duration) return;

    const endTop = endThreshold.getBoundingClientRect().top;
    let ratio: number;

    if (block.classList.contains('hero')) {
      const endAbsolute = endTop + window.scrollY;
      ratio = endAbsolute <= 0 ? 0 : Math.min(window.scrollY / endAbsolute, 1);
    } else {
      if (!startThreshold) return;
      const startTop = startThreshold.getBoundingClientRect().top;
      const scrolled = window.innerHeight - startTop;
      const total = scrolled + endTop;
      ratio = total <= 0 ? 0 : Math.min(Math.max(scrolled / total, 0), 1);
    }

    video.currentTime = video.duration * ratio;

    if (progressBar) {
      progressBar.style.width = `${ratio * 100}%`;
    }
  };

  videoBlocks.forEach((block) => {
    const video = block.querySelector<HTMLVideoElement>('video');
    if (!video) return;

    video.load();
    video.muted = true;

    video.play().then(() => {
      video.pause();
    });

    moveVideo(video, block);

    document.addEventListener('scroll', () => {
      moveVideo(video, block);
    });
  });
})();
