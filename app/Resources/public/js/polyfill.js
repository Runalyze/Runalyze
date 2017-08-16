/*!
 * Polyfill for Math.log2, required for IE
 * @see https://github.com/Runalyze/Runalyze/issues/2203
 */
Math.log2 = Math.log2 || function(x) {
  return Math.log(x) * Math.LOG2E;
};