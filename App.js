function renderPage(pageName) {
  const validPage = PAGES[pageName] ? pageName : "dashboard";

  document.getElementById("page-content").innerHTML = PAGES[validPage];

  //update the active tab
  document.querySelectorAll("#tabs a").forEach(tab => {
    tab.classList.toggle("active", tab.dataset.page === validPage);
  });

  document.title = `ESCHOOL - ${validPage.charAt(0).toUpperCase() + validPage.slice(1)}`;
}

function pageFromPath() {
  const path = window.location.pathname.replace(/^\/+/, "");
  return path || "dashboard";
}

// Intercept tab clicks so the browser doesn't do a full navigation
document.getElementById("tabs").addEventListener("click", e => {
  const link = e.target.closest("a[data-page]");
  if (!link) return;

  e.preventDefault();
  const page = link.dataset.page;

  // add a new url name without requiring a page reload
  history.pushState({ page }, "", `/${page}`);
  renderPage(page);
});

// Handle browser back/forward buttons
window.addEventListener("popstate", () => {
  renderPage(pageFromPath());
});

// Initial render based on whatever URL the page was loaded with
renderPage(pageFromPath());