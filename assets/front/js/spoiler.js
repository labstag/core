export class Spoiler {
    constructor() {
        const lazyBackgrounds = document.querySelectorAll(".spoiler");

        const reveal = (div) => {
            const fullImage = div.getAttribute("data-bg");
            if (!fullImage) return;
            const img = new Image();
            img.src = fullImage;
            img.onload = () => {
                div.style.backgroundImage = `url('${fullImage}')`;
                div.classList.add("loaded");
            };
            img.onerror = () => div.classList.add("loaded");
        };

        if (!('IntersectionObserver' in window)) {
            lazyBackgrounds.forEach(reveal);
            return;
        }

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    reveal(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            root: null,
            rootMargin: "200px",
            threshold: 0.01
        });

        lazyBackgrounds.forEach(div => observer.observe(div));
    }
}