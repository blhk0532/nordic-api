// resources/js/site.js

// 1. Remove page-loading class
document.addEventListener('DOMContentLoaded', () => {
  document.body.classList.remove('page-loading');
});

// 2. GDPR popup handler
document.addEventListener('DOMContentLoaded', function () {
  var gdprKey = 'GalvaniteGDPR';
  var gdprAccepted = localStorage.getItem(gdprKey);
  var popup = document.querySelector('.popup-gdpr');

  if (gdprAccepted === 'ok') {
    if (popup) popup.classList.remove('show-gdpr');
  } else {
    if (popup) popup.classList.add('show-gdpr');
  }

  document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('gdpr')) {
      e.preventDefault();
      localStorage.setItem(gdprKey, 'ok');
      if (popup) {
        popup.classList.remove('show-gdpr');
        popup.style.display = 'none';
      }
    }
  });
});

// 3. Cursor animation (requires GSAP loaded)
const circleElement = document.querySelector('.cursor');
const mouse = { x: 0, y: 0 };
const previousMouse = { x: 0, y: 0 };
const circle = { x: 0, y: 0 };
let currentScale = 0;
let currentAngle = 0;

window.addEventListener('mousemove', (e) => {
  mouse.x = e.x;
  mouse.y = e.y;
});

const speed = 0.2;
const tick = () => {
  circle.x += (mouse.x - circle.x) * speed;
  circle.y += (mouse.y - circle.y) * speed;
  const translateTransform = `translate(${circle.x}px, ${circle.y}px)`;
  const deltaMouseX = mouse.x - previousMouse.x;
  const deltaMouseY = mouse.y - previousMouse.y;
  previousMouse.x = mouse.x;
  previousMouse.y = mouse.y;
  const mouseVelocity = Math.min(Math.sqrt(deltaMouseX ** 2 + deltaMouseY ** 2) * 10, 150);
  const scaleValue = (mouseVelocity / 150) * 0.5;
  currentScale += (scaleValue - currentScale) * speed;
  const scaleTransform = `scale(${1 + currentScale}, ${1 - currentScale})`;
  const angle = Math.atan2(deltaMouseY, deltaMouseX) * 180 / Math.PI;
  if (mouseVelocity > 20) {
    currentAngle = angle;
  }
  const rotateTransform = `rotate(${currentAngle}deg)`;
  circleElement.style.transform = `${translateTransform} ${rotateTransform} ${scaleTransform}`;
  window.requestAnimationFrame(tick);
};

tick();

// Add hover class to cursor
document.addEventListener('DOMContentLoaded', () => {
  function toggleCursorClass(isEntering) {
    document.querySelector('.cursor').classList.toggle('large', isEntering);
  }
  document.querySelectorAll('a').forEach(link => {
    link.addEventListener('mouseenter', () => toggleCursorClass(true));
    link.addEventListener('mouseleave', () => toggleCursorClass(false));
  });
});

// 4. Rocket sparks animation
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('sparks');
  if (!container) return;
  const SPARK_COUNT = 10;
  const MIN_H = 5;
  const MAX_H = 35;
  const MIN_DUR = 1.5;
  const MAX_DUR = 3;
  const SPEED = 2;

  function mapRange(value, inMin, inMax, outMin, outMax) {
    return outMin + (value - inMin) * (outMax - outMin) / (inMax - inMin);
  }

  container.innerHTML = '';
  const C_HEIGHT = container.clientHeight;

  for (let i = 0; i < SPARK_COUNT; i++) {
    const spark = document.createElement('div');
    spark.classList.add('spark');
    const h = Math.random() * (MAX_H - MIN_H) + MIN_H;
    spark.style.height = `${h}px`;
    const centerBias = (Math.random() + Math.random()) / 2;
    spark.style.left = `${centerBias * 100}%`;
    spark.style.bottom = '100%';
    container.appendChild(spark);
    const baseDur = mapRange(h, MIN_H, MAX_H, MAX_DUR, MIN_DUR);
    const duration = baseDur / SPEED;
    gsap.to(spark, {
      y: C_HEIGHT + h,
      duration: duration,
      ease: 'sine.out',
      repeat: -1,
      delay: Math.random() * duration
    });
  }
});

// 5. Puffs random positioning
document.addEventListener('DOMContentLoaded', () => {
  const puffs = document.querySelectorAll('.puff');
  if (!puffs.length) return;
  puffs.forEach(puff => {
    const size = (Math.random() * 6 + 2).toFixed(1);
    const randomTowardCenter = () => (Math.random() + Math.random()) / 1.25;
    const left = (8 * randomTowardCenter() - 3).toFixed(1);
    const top = (3 * randomTowardCenter() + 1).toFixed(1);
    puff.style.height = `${size}rem`;
    puff.style.width = `${size}rem`;
    puff.style.top = `${top}rem`;
    puff.style.left = `${left}rem`;
    puff.style.position = 'absolute';
    puff.style.transition = 'all 0.3s ease-out';
  });
});

// 6. Swiper testimonials
document.addEventListener('DOMContentLoaded', () => {
  if (typeof Swiper !== 'undefined') {
    new Swiper('.swiper-testimonials', {
      direction: 'horizontal',
      loop: true,
      speed: 750,
      slidesPerView: 1.2,
      centeredSlides: true,
      breakpoints: {
        992: { slidesPerView: 1.8 },
        1920: { slidesPerView: 2.5 },
      },
      autoplay: {
        delay: 10000,
        pauseOnMouseEnter: true,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
    });
  }
});

// 7. FAQ JSON-LD schema
window.addEventListener('DOMContentLoaded', () => {
  let faqArray = [];
  let questionElements = document.querySelectorAll('[ms-code-snippet-q]');
  let answerElements = document.querySelectorAll('[ms-code-snippet-a]');

  for (let i = 0; i < questionElements.length; i++) {
    let question = questionElements[i].textContent.trim();
    let answer = '';
    for (let j = 0; j < answerElements.length; j++) {
      if (questionElements[i].getAttribute('ms-code-snippet-q') === answerElements[j].getAttribute('ms-code-snippet-a')) {
        answer = answerElements[j].innerHTML.trim();
        break;
      }
    }
    if (question.length && answer.length) {
      faqArray.push({
        "@type": "Question",
        "name": question,
        "acceptedAnswer": {
          "@type": "Answer",
          "text": answer
        }
      });
    }
  }
  if (faqArray.length) {
    let faqSchema = {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": faqArray
    };
    let script = document.createElement('script');
    script.type = "application/ld+json";
    script.text = JSON.stringify(faqSchema);
    document.head.appendChild(script);
  }
});

// 8. GSAP matchMedia for bento text (requires SplitText & ScrollTrigger)
window.addEventListener('load', () => {
  if (typeof gsap !== 'undefined' && typeof SplitText !== 'undefined') {
    const mm = gsap.matchMedia();
    mm.add('(min-width: 992px) and (orientation: landscape)', () => {
      const boxes = document.querySelectorAll('.bento-box');
      const cleanups = [];
      boxes.forEach(box => {
        const target = box.querySelector('.bento-h');
        if (!target) return;
        const split = new SplitText(target, {
          type: 'lines',
          linesClass: 'split-line',
          mask: 'lines'
        });
        gsap.set(split.lines, { yPercent: 30, opacity: 0 });
        const tl = gsap.timeline({ paused: true });
        tl.to(split.lines, {
          yPercent: 0,
          opacity: 1,
          duration: 0.5,
          ease: 'power2.out',
          stagger: 0.2
        });
        const enter = () => tl.play();
        const leave = () => tl.reverse();
        box.addEventListener('mouseenter', enter);
        box.addEventListener('mouseleave', leave);
        cleanups.push(() => {
          box.removeEventListener('mouseenter', enter);
          box.removeEventListener('mouseleave', leave);
          split.revert();
          tl.kill();
        });
      });
      return () => cleanups.forEach(fn => fn());
    });
  }
});

// 9. Update copyright year
document.addEventListener('DOMContentLoaded', () => {
  const yrSpan = document.querySelector('.current-year');
  if (yrSpan) yrSpan.textContent = new Date().getFullYear();
});