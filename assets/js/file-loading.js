document.addEventListener('DOMContentLoaded', () => {
    lazyLoadElements(document);
    console.log('loaded');
});

function lazyLoadElements(container) {
    if (!container) return;

    // Lazy-load both img elements and background images
    const lazyElements = [
        ...container.querySelectorAll('img[src], img[srcset]'),
        ...container.querySelectorAll('[style*="background-image"]') // for divs with inline background-image style
    ];

    if ('IntersectionObserver' in window) {
        const lazyObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const lazyElement = entry.target;

                    // For <img> elements (with src or srcset)
                    if (lazyElement.tagName === 'IMG') {
                        const highResSrc = lazyElement.getAttribute('src') || lazyElement.getAttribute('data-src');
                        const highResSrcset = lazyElement.getAttribute('srcset') || lazyElement.getAttribute('data-srcset');

                        if (highResSrc) {
                            lazyElement.src = highResSrc;
                        }

                        if (highResSrcset) {
                            lazyElement.srcset = highResSrcset;
                        }
                    }

                    if (lazyElement.style.backgroundImage && lazyElement.style.backgroundImage.startsWith('url')) {
                        const bgImageUrl = lazyElement.getAttribute('data-bg-image');
                        if (bgImageUrl) {
                            lazyElement.style.backgroundImage = `url(${bgImageUrl})`;
                        }
                    }

                    lazyObserver.unobserve(lazyElement);
                }
            });
        });

        lazyElements.forEach((lazyElement) => {
            lazyObserver.observe(lazyElement);
        });
    } else {
        lazyElements.forEach((lazyElement) => {
            if (lazyElement.tagName === 'IMG') {
                const highResSrc = lazyElement.getAttribute('src') || lazyElement.getAttribute('data-src');
                const highResSrcset = lazyElement.getAttribute('srcset') || lazyElement.getAttribute('data-srcset');

                if (highResSrc) {
                    lazyElement.src = highResSrc;
                }

                if (highResSrcset) {
                    lazyElement.srcset = highResSrcset;
                }
            }

            if (lazyElement.style.backgroundImage && lazyElement.style.backgroundImage.startsWith('url')) {
                const bgImageUrl = lazyElement.getAttribute('data-bg-image');
                if (bgImageUrl) {
                    lazyElement.style.backgroundImage = `url(${bgImageUrl})`;
                }
            }
        });
    }
}
