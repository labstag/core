import A11yDialog from 'a11y-dialog';

export class Movie {

  constructor() {
    document.querySelectorAll('.dialog-container').forEach((element) => {
      var dialog = new A11yDialog(element);
      dialog.on('hide', function (element) {
        const modal = element.target;
        if (modal.querySelector('.video') && modal.querySelector('.video').dataset.html != '') {
          modal.querySelector('.video').innerHTML = modal.querySelector('.video').dataset.html;
          modal.querySelector('.video').dataset.html = '';
        }
      });
    });
  }
}