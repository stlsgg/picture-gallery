/**
 * Render a value inside a DOM element.
 *
 * @param {HTMLElement} targetElement - Target element where the value will be
 * rendered.
 * @param {Record<string, string>} effects - A map of element property names
 * and their new values (e.g., { innerText: "Hello", className: "active" }).
 * Each key will be applied directly to the target element if it exists.
 * @returns {void}
 */
export const renderState = (targetElement, effects) => {
  for (const [key, val] of Object.entries(effects)) {
    if (key in targetElement) {
      targetElement[key] = val;
    }
  }
};
