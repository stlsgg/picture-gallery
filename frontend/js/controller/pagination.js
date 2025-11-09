// Модуль, отвечающий за логику пагинации

/**
 * Get first element of a page.
 *
 * @param {number} currentPage - Page number.
 * @param {number} elementsPerPage - Total amount of elements on a page.
 * @returns {number} firstElementIndex - Index of first element, starts with 1.
 */
function getFirstElement(currentPage, elementsPerPage) {
  return elementsPerPage * (currentPage - 1) + 1;
}

/**
 * Get last element of a page.
 *
 * @param {number} currentPage - Current page number.
 * @param {number} elementsPerPage - Total amount of elements on a page.
 * @returns {number} lastElementIndex - Index of last element.
 */
function getLastElement(currentPage, elementsPerPage) {
  return elementsPerPage * currentPage;
}
