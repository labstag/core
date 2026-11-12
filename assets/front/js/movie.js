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

    // Rebuild the URL without empty/0 values on submit
    document.querySelectorAll('form').forEach((form) => {
      if (!form.querySelector('[name="year"]')) {
        return;
      }
      form.addEventListener('submit', (event) => {
        event.preventDefault();
        const params = new URLSearchParams();
        form.querySelectorAll('select, input').forEach((field) => {
          if (field.name && field.value !== '' && field.value !== '0' && field.type !== 'submit' && field.type !== 'reset') {
            params.append(field.name, field.value);
          }
        });
        const url = form.action + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
      });
    });
  }
}