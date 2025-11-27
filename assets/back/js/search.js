export class Search {
  constructor() {
    this.searchModal()
    this.disableFormSubmit()
    this.addToBdd()
  }

  async executeAjax(modalContent, button) {
    const url = button.dataset.url;
    const form = modalContent ? modalContent.querySelector('form') : null;
    const formData = form ? new FormData(form) : new FormData();

    try {
      const response = await fetch(url, {
        method: 'POST',
        body: formData
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
      const button = modalContent ? modalContent.querySelector('.searchdata-modal') : null;
      if (modalContent && button) {
        event.preventDefault();
        this.executeAjax(modalContent, button);
      }
    });
  }

  async searchModal() {
    document.body.addEventListener('click', async (event) => {
      const button = event.target.closest('.searchdata-modal');
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

  addToBdd() {
    document.body.addEventListener('click', async (event) => {
      const button = event.target.closest('.addtobdd');
      if (button) {
        event.preventDefault();
        const url = button.getAttribute('href');
        try {
          const response = await fetch(url, {
            method: 'GET'
          });
          if (response.ok) {

            // Optionally handle success response
            const data = await response.json();
            if (data.status === 'success') {
              const rows = document.querySelectorAll(`tr[data-id="${data.id}"]`);
              if (rows.length > 0) {
                const firstRow = rows[0];
                const tdCount = firstRow.querySelectorAll('td').length;
                const message = typeof data.message === 'object' ? JSON.stringify(data.message) : data.message;
                firstRow.innerHTML = `<td colspan="${tdCount}" class="text-center blink">${message}</td>`;
                setTimeout(() => {
                  firstRow.remove();
                }, 10000);
                for (let i = 1; i < rows.length; i++) {
                  rows[i].remove();
                }
              }
            }
          }
        } catch (error) {
          console.error('Error:', error);
        }
      }
    });
  }
}
