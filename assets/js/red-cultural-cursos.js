(() => {
  const debounce = (fn, waitMs) => {
    let timeoutId = null;
    return (...args) => {
      if (timeoutId) window.clearTimeout(timeoutId);
      timeoutId = window.setTimeout(() => fn(...args), waitMs);
    };
  };

  const initInstance = (root) => {
    const searchInput = root.querySelector("[data-rcp-search]");
    const grid = root.querySelector("[data-rcp-grid]");
    const status = root.querySelector("[data-rcp-status]");
    if (!searchInput || !grid) return;

    const ajaxUrl = root.dataset.ajaxUrl;
    const nonce = root.dataset.nonce;
    const limit = root.dataset.limit || "0";
    if (!ajaxUrl || !nonce) return;

    const initialHtml = grid.innerHTML;
    let controller = null;

    const setStatus = (message) => {
      if (!status) return;
      if (!message) {
        status.textContent = "";
        status.classList.add("hidden");
        return;
      }
      status.textContent = message;
      status.classList.remove("hidden");
    };

    const doSearch = async () => {
      const term = (searchInput.value || "").trim();
      if (term === "") {
        if (controller) controller.abort();
        grid.innerHTML = initialHtml;
        setStatus("");
        return;
      }

      if (controller) controller.abort();
      controller = new AbortController();

      setStatus("Buscando…");

      const body = new URLSearchParams();
      body.set("action", "rcp_red_cultural_cursos_search");
      body.set("nonce", nonce);
      body.set("search", term);
      body.set("limit", String(limit));

      try {
        const res = await fetch(ajaxUrl, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
          body: body.toString(),
          signal: controller.signal,
        });
        const json = await res.json();
        if (!json || !json.success || !json.data) {
          setStatus("No se pudo buscar.");
          return;
        }

        grid.innerHTML = json.data.html || "";
        setStatus("");
      } catch (err) {
        if (err && err.name === "AbortError") return;
        setStatus("No se pudo buscar.");
      }
    };

    searchInput.addEventListener("input", debounce(doSearch, 250));
  };

  const init = () => {
    document.querySelectorAll("[data-rcp-cursos]").forEach(initInstance);
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

