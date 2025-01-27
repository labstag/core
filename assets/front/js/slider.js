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
        spaceBetween: 17,
        centeredSlides: true,
        effect: 'fade',
        lazy: true,
        slidesPerView: 1,
        fadeEffect: {
          crossFade: true
        },
        navigation: {
          nextEl: '.swiper-button-next', // Bouton Next
          prevEl: '.swiper-button-prev', // Bouton Prev
        },
        breakpoints: {
          1200: {
            slidesPerView: 3,
            centeredSlides: false,
            centerInsufficientSlides: true,
          },
        },
      }
    );
  }
}