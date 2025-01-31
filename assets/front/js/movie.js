export class Movie {
  constructor() {
    document.querySelectorAll('.open-modal').forEach(link => {
      link.addEventListener('click', (e) => {
        const movieId = e.currentTarget.getAttribute('data-movie-id');
        const modal = document.getElementById('movie-modal-'+movieId);
        const closeModal = modal.querySelector('.close-modal');

        closeModal.addEventListener('click', () => {
          modal.classList.add('hidden');
        });

        // Optionnel : fermer la modal en cliquant en dehors
        window.addEventListener('click', (e) => {
          if (e.target === modal) {
            if (modal.querySelector('.video').dataset.html != '') {
              modal.querySelector('.video').innerHTML = modal.querySelector('.video').dataset.html;
            }

            modal.classList.add('hidden');
          }
        });

        // Afficher la modal
        modal.classList.remove('hidden');
      });
    });
  }
}