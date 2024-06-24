/*!
 * Start Bootstrap - Freelancer v7.0.7 (https://startbootstrap.com/theme/freelancer)
 * Copyright 2013-2023 Start Bootstrap
 * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-freelancer/blob/master/LICENSE)
 */

/* global bootstrap */

const handleDOMContentLoaded = () => {
  // Navbar shrink function.
  const navbarShrink = () => {
    const navbarCollapsible = document.body.querySelector('#mainNav');
    if (!navbarCollapsible) {
      return;
    }
    if (window.scrollY === 0) {
      navbarCollapsible.classList.remove('navbar-shrink');
    } else {
      navbarCollapsible.classList.add('navbar-shrink');
    }
  };

  // Shrink the navbar.
  navbarShrink();

  // Shrink the navbar when page is scrolled.
  document.addEventListener('scroll', navbarShrink);

  // Activate Bootstrap scrollspy on the main nav element.
  const mainNav = document.body.querySelector('#mainNav');
  if (mainNav) {
    // Initialise ScrollSpy
    // eslint-disable-next-line no-unused-vars
    const scrollSpy = new bootstrap.ScrollSpy(document.body, {
      target: '#mainNav',
      rootMargin: '0px 0px -40%',
    });
  }

  // Collapse responsive navbar when toggler is visible.
  const navbarToggler = document.body.querySelector('.navbar-toggler');
  const responsiveNavItems = [].slice.call(
    document.querySelectorAll('#navbarResponsive .nav-link'),
  );

  responsiveNavItems.forEach((responsiveNavItem) => {
    responsiveNavItem.addEventListener('click', () => {
      if (window.getComputedStyle(navbarToggler).display !== 'none') {
        navbarToggler.click();
      }
    });
  });
};

window.addEventListener('DOMContentLoaded', handleDOMContentLoaded);
