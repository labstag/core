import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';
import '@nm/swiper/swiper-bundle.min.css';
export class Slider {
  constructor(element, nextbutton, prevbutton)
  {
    new Swiper(
      element,
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
          nextEl: nextbutton, // Bouton Next
          prevEl: prevbutton, // Bouton Prev
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