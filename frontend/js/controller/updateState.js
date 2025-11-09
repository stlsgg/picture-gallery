/**
 * Create a new state object by merging the current state with updates.
 *
 * @param {Object} object - Current state object.
 * @param {Object} newState - Partial state object containing updates.
 * @returns {Object} - A new merged state object.
 */
export const updateState = (object, newState) => {
  object = { ...object, ...newState };
  return object;
};
