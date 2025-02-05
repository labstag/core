export class Movie {
  constructor() {
    document.querySelectorAll('.open-modal').forEach(link => {
      link.addEventListener('click', (e) => {
        const movieId = e.currentTarget.getAttribute('data-movie-id');
        const modal = document.getElementById('movie-modal-'+movieId);
        const closeModal = modal.querySelector('.close');

        closeModal.addEventListener('click', (element) => {
          let modal = element.currentTarget.closest('.modal_movie');
          this.closeModal(modal);
        });

        // Optionnel : fermer la modal en cliquant en dehors
        window.addEventListener('click', (e) => {
          if (e.target === modal) {
            this.closeModal(modal);
          }
        });

        // Afficher la modal
        modal.classList.remove('hidden');
      });
    });
  }

  closeModal(modal)
  {
    if (modal.querySelector('.video') && modal.querySelector('.video').dataset.html != '') {
      modal.querySelector('.video').innerHTML = modal.querySelector('.video').dataset.html;
    }

    modal.classList.add('hidden');
  }
}