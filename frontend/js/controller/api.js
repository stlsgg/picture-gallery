// Модуль по API

/**
 * Check API connection.
 *
 * @param {string} APIUrl - URL to API for test connection.
 * @returns {boolean} connected - true if connected, otherwise false.
 */
export async function checkAPI(APIUrl = "http://api.gg.ru") {
  try {
    const api = await fetch(`${APIUrl}/check`);
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
 * Fetch data about images.
 *
 * @param {number} firstIdx - Index of the first element (inclusive).
 * @param {number} lastIdx - Index of the last element (inclusive).
 * @param {string} url - API url for fetching.
 * @returns {Promise<array|false>} images - Array of image data or false on failure.
 */
export async function fetchImages(firstIdx, lastIdx, url) {
  try {
    const all = await fetchDB(url);

    const maxIdx = all.length;
    lastIdx = Math.min(lastIdx, maxIdx);

    return all.slice(firstIdx - 1, lastIdx);
  } catch (error) {
    console.error(`Error occured while trying to fetch images: ${error}`);
    return false;
  }
}

/**
 * Fetch data from DataBase.
 *
 * @param {string} url - API url for fetching.
 * @returns {Promise<array|false>} data - Array of image data or false on failure.
 */
export async function fetchDB(url) {
  if (!url) {
    console.error(`no url provided, abort getImages`);
    return false;
  }
  try {
    return await fetch(`${url}/images`)
      .then((response) => {
        if (!response.ok)
          throw new Error(
            `HTTP Error when fetching DataBase: ${response.status}`,
          );
        return response.json();
      })
      .then((data) => Object.values(data.data));
  } catch (error) {
    throw new Error(`Error when trying to fetch DataBase: ${error}`);
  }
}
