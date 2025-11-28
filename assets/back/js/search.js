export class Search {
  constructor() {
    this.searchModal()
    this.disableFormSubmit()
    this.addToBdd()
  }

  initData(modalContent) {
    if (typeof this.isInitialized !== 'undefined') {
      modalContent.querySelector('.results').innerHTML = this.isInitialized;
      return;
    }
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = modalContent.querySelector('.results').innerHTML;

    this.isInitialized = tempDiv.innerHTML;

  }

  addInResult(resultsContainer, data) {
    if (resultsContainer) {
      const table = resultsContainer.querySelector('table');
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = data;
      const newTable = tempDiv.querySelector('table');
      // Si newTable contient .datagrid-empty, on stoppe le traitement
      if (newTable.classList.contains('datagrid-empty')) {
        return;
      }

      // Mise à jour de l'attribut data-page
      const newPage = newTable.getAttribute('data-page');
      if (newPage) {
        table.setAttribute('data-page', newPage);
      }

      if (table.classList.contains('datagrid-empty')) {
        table.classList.remove('datagrid-empty');
        if (!table.querySelector('thead')) {
          table.appendChild(newTable.querySelector('thead'));
          const existingTbody = table.querySelector('tbody');
          if (existingTbody) {
            existingTbody.remove();
          }
          table.appendChild(newTable.querySelector('tbody'));
        }
      } else {
        // Ajouter les nouvelles lignes au tbody existant
        const newTbody = newTable.querySelector('tbody');
        const existingTbody = table.querySelector('tbody');
        if (newTbody && existingTbody) {
          existingTbody.append(...newTbody.children);
        }
      }

      const tbody = table.querySelector('tbody');
      if (tbody.dataset.scrollListenerAttached === 'true') {
        return;
      }

      tbody.dataset.scrollListenerAttached = 'true';
      tbody.addEventListener("scroll", () => {
        const scrollPosition = tbody.scrollTop + tbody.clientHeight;

        // Hauteur totale du contenu
        const scrollHeight = tbody.scrollHeight;

        // Marge d’erreur (évite les micro-décalages)
        if (scrollPosition >= scrollHeight - 5) {
          const currentPage = parseInt(table.getAttribute('data-page')) || 1;
          const button = resultsContainer.closest('.modal-content').querySelector('.searchdata-modal');
          this.executeAjax(resultsContainer.closest('.modal-content'), button, currentPage + 1);
        }
      });
    }
  }

  async executeAjax(modalContent, button, page) {
    const url = new URL(button.dataset.url, window.location.origin);
    url.searchParams.set('page', page);
    const form = modalContent ? modalContent.querySelector('form') : null;
    const formData = form ? new FormData(form) : new FormData();

    try {
      const response = await fetch(url, {
        method: 'POST',
        body: formData
      });
      const data = await response.text();
      const resultsContainer = modalContent ? modalContent.querySelector('.results') : null;
      this.addInResult(resultsContainer, data);
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
        this.executeAjax(modalContent, button, 1);
      }
    });
  }

  async searchModal() {
    document.body.addEventListener('click', async (event) => {
      const button = event.target.closest('.searchdata-modal');
      if (button) {
        event.preventDefault();
        this.initData(button.closest('.modal-content'));
        this.executeAjax(button.closest('.modal-content'), button, 1);
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
