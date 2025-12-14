import TomSelect from 'tom-select/dist/js/tom-select.complete.min';
/* global bootstrap */
export class Modal {
  constructor() {
    this.init()
  }
  init() {
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-action="show-modal"]');
      if (!btn) return;

      e.preventDefault();
      const url = btn.getAttribute('href');
      const html = await (await fetch(url)).text();

      const modalContent = document.getElementById('easyadmin-modal-content');
      if (!modalContent) return; // sécuriser
      modalContent.innerHTML = html;
      const selects = modalContent.querySelectorAll('select');
        selects.forEach(selectElement => {
          if (typeof TomSelect !== 'undefined') {
            // 4. Vérifiez si TomSelect n'est pas déjà initialisé
            if (!selectElement.tomselect) {

              // 5. Initialisation de TomSelect
              new TomSelect(selectElement, {
                // **OPTION CRUCIALE POUR LES MODALES**
                // Ceci permet au dropdown de s'afficher par-dessus la modale.
                dropdownParent: 'body',
                // Ajoutez d'autres options TomSelect si nécessaire
                // par exemple : maxItems, plugins, etc.
              });
            }
          }else{
            console.log('aa');
          }
        });
      const modal = new bootstrap.Modal(document.getElementById('easyadmin-modal'));
      modal.show();
    });
  }
}
