export class Form {

  constructor() {
    document.querySelectorAll('form').forEach((element) => {
      if (element.getAttribute('method').toLowerCase() == 'get') {
        element.addEventListener('submit', (event) => {
          event.preventDefault();
          const formData = new FormData(event.target);
          const form = event.target;
          const params = new URLSearchParams();

          for (const [key, value] of formData.entries()) {
            if (value.trim() !== "") {
              params.append(key, value);
            }
          }

          if (params.toString() == '') {
            window.location.href = form.action;
          } else {
            window.location.href = form.action + '?' + params.toString();
          }
        });
      }
    });
  }
}