export class Game {
  constructor() {
    this.searchModal()
  }

  async searchModal() {
    document.body.addEventListener('click', async (event) => {
      const button = event.target.closest('.another_platforms');
      if (button) {
        event.preventDefault();
        this.executeAjax(button.closest('.modal-content'), button);
        const modal = button.closest('.modal');
        if (modal) {
          const bsModal = bootstrap.Modal.getInstance(modal);
          if (bsModal) {
            modal.addEventListener('hidden.bs.modal', () => {
              window.location.reload();
            }, { once: true });
          }
        }
      }
    });
  }

  async executeAjax(modalContent, button) {
    const url = button.dataset.url;
    const form = modalContent ? modalContent.querySelector('form') : null;

    if (!form) {
      console.error('No form found in modal content');
      return;
    }

    const formData = new FormData(form);

    try {
      const response = await fetch(url, {
        method: 'POST',
        body: formData
      });
      const data = await response.json();
      const infoDiv = modalContent.querySelector('.info');
      if (infoDiv) {
        infoDiv.innerHTML = data.message || 'No message returned';
      }
    } catch (error) {
      console.error('Error:', error);
    }
  }
}
