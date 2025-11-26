export class Game {
  constructor() {
    this.searchModal()
    this.disableFormSubmit()
  }

  async executeAjax(modalContent, button)
  {
    const url = button.dataset.url;
    const params = new URLSearchParams();
    // Récupérer tous les inputs et selects du formulaire
    const inputs = modalContent ? modalContent.querySelectorAll('input, select') : [];
    inputs.forEach(element => {
      if (element.name && element.value) {
        params.append(element.name, element.value);
      }
    });
    
    const urlWithParams = `${url}?${params.toString()}`;
    try {
      const response = await fetch(urlWithParams, {
        method: 'GET'
      });
      const data = await response.text();
      const resultsContainer = modalContent ? modalContent.querySelector('.results') : null;
      if (resultsContainer) {
        resultsContainer.innerHTML = data;
      }
    } catch (error) {
      console.error('Error:', error);
    }
  }

  disableFormSubmit() {
    document.body.addEventListener('submit', (event) => {
      const form = event.target;
      const modalContent = form.closest('.modal-content');
      const button = modalContent ? modalContent.querySelector('.searchgame-modal') : null;
      if (modalContent && button) {
        event.preventDefault();
        this.executeAjax(modalContent, button);
      }
    });
  }

  async searchModal() {
    document.body.addEventListener('click', async (event) => {
      const button = event.target.closest('.searchgame-modal');
      if (button) {
        event.preventDefault();
        this.executeAjax(button.closest('.modal-content'), button);
      }
    });
  }
}
