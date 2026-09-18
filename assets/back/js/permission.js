export class Permission {
  constructor() {
    this.toggle()
    this.table()
  }
  table() {
    document.querySelectorAll('.table-permissions tbody').forEach(tbody => {
      const headerRow = tbody.querySelector('tr:first-child');

      if (!headerRow) return;

      headerRow.style.cursor = 'pointer';

      headerRow.addEventListener('click', function () {
        // Toutes les lignes sauf la première (le header du groupe)
        const rows = Array.from(tbody.querySelectorAll('tr:not(:first-child)'));

        rows.forEach(row => {
          row.style.display = row.style.display === 'none' ? '' : 'none';
        });
      });
    });
  }
  toggle() {
    document.querySelectorAll(".permission-toggle").forEach(
      (element) => {
        element.addEventListener('change', function () {
          fetch(this.dataset.toggleUrl, {
            method: 'POST'
          })
            .then(response => response.json())
            .then(result => {
              console.log('Serveur:', result);
            })
            .catch(error => {
              console.error('Erreur AJAX:', error);
            });
        });
      }
    );
  }
}
