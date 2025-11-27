export class Search {
  constructor() {
    this.searchModal()
    this.disableFormSubmit()
    this.addToBdd()
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
                firstRow.innerHTML = `<td colspan="${tdCount}" class="text-center">${message}</td>`;
                // Remove other rows except the first one
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
