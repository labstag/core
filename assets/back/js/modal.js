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
      const modal = new bootstrap.Modal(document.getElementById('easyadmin-modal'));
      modal.show();
    });
  }
}
