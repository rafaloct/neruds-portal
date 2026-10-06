/**
 * Fetch with exponential backoff retry logic.
 * Retries up to 3 times on network/5xx errors.
 */
(function (window) {
  window.fetchWithRetry = async function fetchWithRetry(
    url,
    options = {},
    maxRetries = 3,
  ) {
    let lastError;

    for (let attempt = 0; attempt < maxRetries; attempt++) {
      try {
        const response = await fetch(url, options);

        if (response.ok) {
          return response;
        }

        if (response.status >= 500) {
          lastError = new Error(`Server error: ${response.status}`);
          if (attempt < maxRetries - 1) {
            await delay(backoffMs(attempt));
            continue;
          }
        }

        return response;
      } catch (error) {
        lastError = error;

        if (attempt < maxRetries - 1) {
          await delay(backoffMs(attempt));
        }
      }
    }

    throw lastError;
  };

  function backoffMs(attempt) {
    const baseMs = 1000;
    const exponential = baseMs * Math.pow(2, attempt);
    const jitter = Math.random() * 200 - 100;
    return Math.max(0, exponential + jitter);
  }

  function delay(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  window.fetchWithRetry.jitter = 200;
  window.fetchWithRetry.baseMs = 1000;
})(window);
