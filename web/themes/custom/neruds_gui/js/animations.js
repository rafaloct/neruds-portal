/**
 * @file animations.js
 * Micro-interações e Animações
 * Fade in, Slide in, Scale, Rotate
 */

(function() {
  'use strict';

  window.NERUDS = window.NERUDS || {};

  document.addEventListener('DOMContentLoaded', function() {
    setupIntersectionAnimations();
  });

  /**
   * Animar elementos ao entrar na viewport
   */
  function setupIntersectionAnimations() {
    if (!('IntersectionObserver' in window)) {
      return;
    }

    const animationElements = document.querySelectorAll('[data-animate]');

    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          const animationType = entry.target.getAttribute('data-animate');
          const delay = parseFloat(entry.target.getAttribute('data-animate-delay') || 0);

          setTimeout(function() {
            entry.target.classList.add('animated', 'animate-' + animationType);
          }, delay * 1000);

          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '50px'
    });

    animationElements.forEach(function(el) {
      observer.observe(el);
    });
  }

  /**
   * Parley Animation - Stagger effect
   */
  window.NERUDS.staggerAnimateChildren = function(parentSelector, delay) {
    delay = delay || 100;
    const parent = document.querySelector(parentSelector);

    if (!parent) return;

    const children = parent.children;
    for (let i = 0; i < children.length; i++) {
      setTimeout(function() {
        children[i].classList.add('animated');
      }, delay * i);
    }
  };

  /**
   * Pulse Animation
   */
  window.NERUDS.pulse = function(element) {
    element.classList.add('pulse');
    setTimeout(function() {
      element.classList.remove('pulse');
    }, 600);
  };

})();
