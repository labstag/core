// import A11yDialog from 'a11y-dialog';
export class Video
{
  constructor()
  {
    document.querySelectorAll('.js-btnvideo').forEach(
      (btn) => {
        btn.addEventListener('click', (event) => {
          event.preventDefault();
          
          // closest event .video
          let video = btn.closest('.video');
          const player = document.createElement('iframe');
          player.setAttribute('class', 'iframe');
          player.setAttribute('frameborder', 0);
          player.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture');
          player.setAttribute('src', video.getAttribute('data-src'));
          let html = video.innerHTML;
          video.dataset.html = html;
          video.replaceChildren(player);
        });
      }
    );
  }
}