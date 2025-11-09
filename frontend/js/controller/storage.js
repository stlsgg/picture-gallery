// Модуль, отвечающий за работу с локальным хралищием

/**
 * Save a value to localStorage.
 *
 * @param {string} key - Storage key.
 * @param {*} state - Any serializable value to store.
 * @returns {void}
 */
export const saveState = (key, state) => {
  localStorage.setItem(key, JSON.stringify(state));
};

/**
 * Load a value from localStorage.
 *
 * @param {string} key - Storage key.
 * @returns {*|null} - Parsed stored value, or null if not found or parsing
 * failed.
 */
export const loadState = (key) => {
  try {
    const saved = localStorage.getItem(key);
    return saved ? JSON.parse(saved) : null;
  } catch (error) {
    console.error("Error loading state:", error);
    return null;
  }
};

/**
 * Clear localStorage.
 *
 * @param {string} key - Storage key.
 * @returns {void}
 */
export const clearState = (key) => {
  localStorage.removeItem(key);
};
