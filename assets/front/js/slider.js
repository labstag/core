import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';
import '@nm/swiper/swiper-bundle.min.css';
export class Slider {
  constructor(element, nextbutton, prevbutton) {
    this.init(element, nextbutton, prevbutton);
  }
  init(element, nextbutton, prevbutton) {

    new Swiper(
      '.swiper',
      {
        modules: [Navigation],
        slidesPerView: 3,
        spaceBetween: 20,
        loop: true,
        centeredSlides: false,
        navigation: {
          nextEl: '.swiper-button-next', // Bouton Next
          prevEl: '.swiper-button-prev', // Bouton Prev
        },
        breakpoints: {
          320: {
            slidesPerView: 1, // 1 slide visible sur petit écran
            spaceBetween: 0,
          },
          768: {
            slidesPerView: 2, // 2 slides visibles sur tablette
            spaceBetween: 15,
          },
          1024: {
            slidesPerView: 3, // 3 slides visibles sur desktop
            spaceBetween: 20,
          },
        },
      }
    );
  }
}