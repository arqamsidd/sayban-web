/**
 * Sayban.pk — lightweight gallery lightbox (no dependencies).
 * Triggers on `.sb-lb` or `.sb-lightbox` links. Groups by data-group /
 * data-sbgal. Full image = data-full || href. Prev/next + keyboard + counter.
 */
(function () {
  var SEL = '.sb-lb, .sb-lightbox';
  var ov, imgEl, countEl, group = [], idx = 0;

  function grpOf(n) { return n.getAttribute('data-group') || n.getAttribute('data-sbgal') || 'default'; }

  function build() {
    ov = document.createElement('div');
    ov.className = 'sb-lb-overlay';
    ov.setAttribute('hidden', '');
    ov.innerHTML =
      '<button type="button" class="sb-lb-btn sb-lb-close" aria-label="Close">×</button>' +
      '<button type="button" class="sb-lb-btn sb-lb-prev" aria-label="Previous image">‹</button>' +
      '<div class="sb-lb-stage"><img class="sb-lb-img" alt=""><div class="sb-lb-count"></div></div>' +
      '<button type="button" class="sb-lb-btn sb-lb-next" aria-label="Next image">›</button>';
    document.body.appendChild(ov);
    imgEl = ov.querySelector('.sb-lb-img');
    countEl = ov.querySelector('.sb-lb-count');
    ov.querySelector('.sb-lb-close').addEventListener('click', close);
    ov.querySelector('.sb-lb-prev').addEventListener('click', function (e) { e.stopPropagation(); step(-1); });
    ov.querySelector('.sb-lb-next').addEventListener('click', function (e) { e.stopPropagation(); step(1); });
    ov.addEventListener('click', function (e) {
      if (e.target === ov || e.target.classList.contains('sb-lb-stage')) { close(); }
    });
    document.addEventListener('keydown', function (e) {
      if (!ov || ov.hasAttribute('hidden')) { return; }
      if (e.key === 'Escape') { close(); }
      else if (e.key === 'ArrowLeft') { step(-1); }
      else if (e.key === 'ArrowRight') { step(1); }
    });
  }

  function show() {
    var multi = group.length > 1;
    imgEl.src = group[idx];
    countEl.textContent = multi ? (idx + 1) + ' / ' + group.length : '';
    ov.querySelector('.sb-lb-prev').style.display = multi ? '' : 'none';
    ov.querySelector('.sb-lb-next').style.display = multi ? '' : 'none';
  }
  function step(d) { idx = (idx + d + group.length) % group.length; show(); }
  function close() { ov.setAttribute('hidden', ''); document.documentElement.classList.remove('sb-lb-open'); }
  function open(items, start) {
    if (!ov) { build(); }
    group = items; idx = start; show();
    ov.removeAttribute('hidden'); document.documentElement.classList.add('sb-lb-open');
  }

  document.addEventListener('click', function (e) {
    var t = e.target.closest(SEL);
    if (!t) { return; }
    e.preventDefault();
    var grp = grpOf(t);
    var nodes = [].slice.call(document.querySelectorAll(SEL)).filter(function (n) { return grpOf(n) === grp; });
    var items = nodes.map(function (n) { return n.getAttribute('data-full') || n.getAttribute('href'); }).filter(Boolean);
    if (!items.length) { return; }
    open(items, Math.max(0, nodes.indexOf(t)));
  });
})();
