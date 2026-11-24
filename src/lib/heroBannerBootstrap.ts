export interface HeroSlide {
  id: string;
  eyebrow: string;
  title: string;
  subtitle: string;
  primaryButtonText: string;
  primaryButtonLink: string;
  secondaryButtonText?: string;
  secondaryButtonLink?: string;
  image: string;
  mobileImage?: string;
  imageAlt: string;
  overlayStyle: string;
  textColor: string;
  alignment: 'left' | 'center';
}

// Read prerendered data before createApp.mount clears the existing HTML.
function readInitialSlides(): HeroSlide[] {
  if (typeof document === 'undefined' || location.pathname !== '/') return [];
  try {
    const value = JSON.parse(document.querySelector('[data-hero-slides]')?.getAttribute('data-hero-slides') || '[]');
    const fields = ['id', 'eyebrow', 'title', 'subtitle', 'primaryButtonText', 'primaryButtonLink', 'image', 'imageAlt', 'overlayStyle', 'textColor'];
    return Array.isArray(value) && value.every(slide => slide && fields.every(field => typeof slide[field] === 'string')) ? value : [];
  } catch { return []; }
}
let initialSlides = readInitialSlides();
export function takeInitialHeroSlides(): HeroSlide[] {
  const slides = initialSlides;
  initialSlides = [];
  return slides;
}
