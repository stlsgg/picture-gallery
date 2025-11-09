/**
 * Render a value inside a DOM element.
 *
 * @param {HTMLElement} targetElement - Target element where the value will be
 * rendered.
 * @param {string|number} value - Value to insert into the element's innerHTML.
 * @returns {void}
 */
export const renderState = (targetElement, value) => {
  targetElement.innerHTML = value;
};
