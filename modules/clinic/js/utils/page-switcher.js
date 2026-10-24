// ─── Department Select Helper (exported for role.js and position.js) ──────────

  export function getFreshDepartmentSelect() {
      const departmentSelect = document.getElementById('department');
      if (!departmentSelect) return null;

      if (departmentSelect.dataset.init) return departmentSelect;

      const fresh = departmentSelect.cloneNode(true);
      fresh.dataset.init = 'true';
      departmentSelect.parentNode.replaceChild(fresh, departmentSelect);
      return fresh;
  }

  // ─── Main ─────────────────────────────────────────────────────────────────────

  document.addEventListener('DOMContentLoaded', function () {

    // I-execute ang lahat ng <script> sa loob ng bagong content.
    // (Hindi pinapatakbo ng browser ang <script> na nilagay gamit ang innerHTML.)
    // Sunud-sunod ang takbo para tama ang order (external muna bago ang inline na gumagamit nito).
    const loadedExternal = new Set();

    function runScripts(container) {
      const scripts = Array.from(container.querySelectorAll('script'));

      return scripts.reduce(function (chain, old) {
        return chain.then(function () {
          return new Promise(function (resolve) {
            const src = old.getAttribute('src');

            // Huwag nang i-load ulit ang external library na na-load na (hal. jsPDF)
            if (src && loadedExternal.has(src)) { resolve(); return; }

            const s = document.createElement('script');
            Array.from(old.attributes).forEach(function (a) { s.setAttribute(a.name, a.value); });

            if (src) {
              s.async = false;
              s.onload  = function () { loadedExternal.add(src); resolve(); };
              s.onerror = function () { console.error('Script failed to load:', src); resolve(); };
              old.replaceWith(s);
            } else {
              s.textContent = old.textContent;
              try { old.replaceWith(s); } catch (err) { console.error('Inline script error', err); }
              resolve();
            }
          });
        });
      }, Promise.resolve());
    }

    // ─── Page Fetching ───────────────────────────────────────────────────────────

    let requestId = 0; // para hindi mag-overwrite ang lumang response kapag mabilis ang click

    function fetchPage(page, push = true) {
      const container = document.querySelector('.container');
      if (!container) return;

      const myRequest = ++requestId;

      fetch(`${window.CLINIC_BASE}/page-loader.php?page=${encodeURIComponent(page)}`, {
        credentials: 'same-origin',
        cache: 'no-store'
      })
        .then(function (response) {
          if (!response.ok) throw new Error('Network error');
          const rendered = response.headers.get('X-Rendered-Page') || page;
          return response.text().then(function (html) {
            return { html: html, rendered: rendered };
          });
        })
        .then(function (result) {
          if (myRequest !== requestId) return; // may mas bago nang request

          container.innerHTML = result.html;
          updateActiveLink(result.rendered);

          if (push) {
            history.pushState({ page: result.rendered }, '', '?page=' + encodeURIComponent(result.rendered));
          }

          // Tabs/forms muna bago patakbuhin ang scripts ng page,
          // para hindi mabura ng cloneNode ang listeners na ilalagay ng page script.
          initTabs();
          initForms();

          return runScripts(container).then(function () {
            window.dispatchEvent(new CustomEvent('page:loaded', { detail: { page: result.rendered } }));
          });
        })
        .catch(function (err) {
          console.error('Page switch failed', err);
        });
    }

    // ─── Active Link ─────────────────────────────────────────────────────────────

    function updateActiveLink(page) {
      document.querySelectorAll('.menu-link, .active-menu-link').forEach(function (el) {
        el.className = 'menu-link';
      });
      var a = document.querySelector('[data-page="' + page + '"]');
      if (a) a.className = 'active-menu-link';
    }

    // ─── Reinitialize Page Scripts ────────────────────────────────────────────────

    function reinitPage(page) {
      initTabs();
      initForms();

      window.dispatchEvent(new CustomEvent('page:loaded', { detail: { page: page } }));
    }

    // ─── Tab Switcher ─────────────────────────────────────────────────────────────

    function initTabs() {
      const tabItems = document.querySelectorAll('.tab-item');
      const tabContents = document.querySelectorAll('.tab-content');

      if (!tabItems.length) return;

      tabItems.forEach(function (tab) {
        tab.addEventListener('click', function () {
          tabItems.forEach(function (t) { t.classList.remove('active'); });
          tabContents.forEach(function (c) { c.classList.remove('active'); });

          tab.classList.add('active');
          const target = document.getElementById(tab.getAttribute('data-tab'));
          if (target) target.classList.add('active');
        });
      });
    }

    // ─── Form Submissions ─────────────────────────────────────────────────────────

    function initForms() {
      const forms = document.querySelectorAll('form:not([data-custom-handler])');

      forms.forEach(function (form) {
        const fresh = form.cloneNode(true);
        form.parentNode.replaceChild(fresh, form);

        fresh.addEventListener('submit', function (e) {
          e.preventDefault();
          const formData = new FormData(fresh);
          const action = fresh.getAttribute('action') || window.location.href;

          fetch(action, {
            method: fresh.getAttribute('method') || 'POST',
            body: formData,
            credentials: 'same-origin'
          })
            .then(function (response) {
              if (!response.ok) throw new Error('Form submission failed');
              return response.text();
            })
            .then(function (result) {
              console.log('Form submitted successfully', result);
              const current = new URL(location).searchParams.get('page') || 'dashboard-overview';
              fetchPage(current, false);
            })
            .catch(function (err) {
              console.error('Form error', err);
            });
        });
      });
    }

    // ─── Sidebar Click Intercept ──────────────────────────────────────────────────

    document.body.addEventListener('click', function (e) {
      var a = e.target.closest('a[data-page]');
      if (!a) return;
      e.preventDefault();
      fetchPage(a.getAttribute('data-page'));
    });

    // ─── Back / Forward ───────────────────────────────────────────────────────────

    window.addEventListener('popstate', function (e) {
      var page = (e.state && e.state.page) || new URL(location).searchParams.get('page') || 'dashboard-overview';
      fetchPage(page, false);
    });

    // ─── Initial Load ─────────────────────────────────────────────────────────────

    var initial = new URL(location).searchParams.get('page') || 'dashboard-overview';
    updateActiveLink(initial);
    initTabs();
    initForms();

  });