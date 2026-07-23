((): void => {
  const videoBlocks = document.querySelectorAll<HTMLElement>('.video-block');

  const updateProgressBar = (video: HTMLVideoElement, progressBar: HTMLElement | null): void => {
    if (!progressBar) return;
    const ratio = video.currentTime / video.duration;
    progressBar.style.width = `${ratio * 100}%`;
  };

  const updatePlayButton = (video: HTMLVideoElement, playButton: HTMLElement | null): void => {
    if (!playButton) return;
    playButton.classList.toggle('playing', !video.paused);
  };

  videoBlocks.forEach((videoBlock) => {
    const video = videoBlock.querySelector<HTMLVideoElement>('video');
    if (!video) return;

    const playButton = videoBlock.querySelector<HTMLElement>('.play-pause');
    const progressBar = videoBlock.querySelector<HTMLElement>('.bar');

    video.addEventListener('timeupdate', () => {
      updateProgressBar(video, progressBar);
      updatePlayButton(video, playButton);
    });

    playButton?.addEventListener('click', () => {
      if (video.paused) {
        video.play();
      } else {
        video.pause();
      }
      updatePlayButton(video, playButton);
    });
  });
})();
