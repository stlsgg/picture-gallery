/**
 * Map DOM element IDs to actual DOM elements.
 *
 * @param {Object.<string, string>} constantsObject - Object where keys are
 * element names and values are their DOM IDs.
 * @returns {Object.<string,  HTMLElement|null>} - Object where keys match the
 * input and values are found DOM elements (or null if not found).
 */
export const getDOMElements = (constantsObject) => {
  const res = {};

  for (const [elemName, elemId] of Object.entries(constantsObject)) {
    res[elemName] = document.getElementById(elemId) || null;
  }

  return res;
};
