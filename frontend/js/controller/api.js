// Модуль по API

/**
 * Check API connection
 *
 * @param {string} APIUrl - URL to API for test connection.
 * @returns {boolean} connected - true if connected, otherwise false.
 */
export async function checkAPI(APIUrl = "http://api.gg.ru") {
  try {
    const api = await fetch(`${APIUrl}/api/check`);
    if (!api.ok) {
      console.error(`http error while testing api connection: ${api.status}`);
      console.log(`abort getImages.`);
      return false;
    }
  } catch (err) {
    console.error(`network error while testing api connection: ${err}`);
    console.log(`abort getImages.`);
    return false;
  }
  return true;
}

/**
 * Fetch data about images
 *
 * @param {number} firstIdx - Index of the first element (inclusive).
 * @param {number} lastIdx - Index of the last element (inclusive).
 * @param {string} url - API url for fetching.
 * @returns {Promise<array|false>} images - Array of image data or false on failure.
 */
export async function fetchImages(firstIdx, lastIdx, url) {
  if (!url) {
    console.error(`no url provided, abort getImages`);
    return false;
  }

  const requests = [];
  for (let i = firstIdx; i <= lastIdx; i++) {
    requests.push(fetch(`${url}/api/images/${i}`).then((res) => res.json()));
  }
  const images = [];
  const results = await Promise.allSettled(requests);
  results.forEach((result) => {
    if (result.status === "fulfilled" && result.value?.data)
      images.push(result.value.data);
  });

  return images;
}
